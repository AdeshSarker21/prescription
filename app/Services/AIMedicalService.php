<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\LaboratoryTest;
use App\Models\Medicine;
use App\Models\MedicalHistoryCondition;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIMedicalService
{
    protected string $provider;
    protected string $disclaimer;

    public function __construct()
    {
        $this->provider = config('ai.provider', 'openai');
        $this->disclaimer = config('ai.disclaimer', '');
    }

    public function isAIAvailable(): bool
    {
        $apiKey = config('ai.openai.api_key');
        return $this->provider === 'openai'
            && !empty($apiKey)
            && $apiKey !== 'your-api-key-here'
            && str_starts_with($apiKey, 'sk-');
    }

    public function chat(string $message, ?int $doctorId = null, ?int $patientId = null, array $extraContext = []): array
    {
        $systemPrompt = $this->buildSystemPrompt($doctorId, $patientId, $extraContext);
        $userMessage = $this->buildUserPrompt($message, $patientId);

        if ($this->isAIAvailable()) {
            return $this->callOpenAI($systemPrompt, $userMessage);
        }

        return $this->smartFallback($message, $doctorId, $patientId, $extraContext);
    }

    public function suggestDiagnosis(array $complaintNames, ?int $doctorId = null, ?int $patientId = null): array
    {
        $message = 'Based on the following complaints/symptoms, suggest possible diagnoses: ' . implode(', ', $complaintNames);
        return $this->chat($message, $doctorId, $patientId, ['mode' => 'diagnosis', 'complaints' => $complaintNames]);
    }

    public function checkInteractions(array $medicineNames, ?int $doctorId = null, ?int $patientId = null): array
    {
        $message = 'Check for drug interactions between the following medicines: ' . implode(', ', $medicineNames);
        return $this->chat($message, $doctorId, $patientId, ['mode' => 'interactions', 'medicines' => $medicineNames]);
    }

    public function suggestMedicines(string $diagnosis, ?int $doctorId = null, ?int $patientId = null): array
    {
        $message = 'Suggest appropriate medicines, dosage, frequency, and duration for the following diagnosis: ' . $diagnosis;
        return $this->chat($message, $doctorId, $patientId, ['mode' => 'medicines', 'diagnosis' => $diagnosis]);
    }

    public function suggestTests(string $symptoms, ?string $diagnosis = null, ?int $doctorId = null, ?int $patientId = null): array
    {
        $message = 'Suggest relevant laboratory tests for the following: ' . $symptoms;
        if ($diagnosis) {
            $message .= ' Diagnosis: ' . $diagnosis;
        }
        return $this->chat($message, $doctorId, $patientId, ['mode' => 'tests', 'symptoms' => $symptoms]);
    }

    public function analyzePatient(int $patientId, int $doctorId): array
    {
        $patient = Patient::where('doctor_id', $doctorId)->find($patientId);
        if (!$patient) {
            return ['reply' => 'Patient not found.', 'suggestions' => [], 'warnings' => []];
        }

        $context = $this->buildPatientContext($patient);
        $message = 'Provide a comprehensive analysis of this patient including: treatment history summary, current conditions, medication review, and recommendations for follow-up.';

        return $this->chat($message, $doctorId, $patientId, ['mode' => 'analysis', 'patient_context' => $context]);
    }

    public function contextualQuery(string $query, ?int $doctorId = null, ?int $patientId = null): array
    {
        return $this->chat($query, $doctorId, $patientId, ['mode' => 'general']);
    }

    protected function buildSystemPrompt(?int $doctorId = null, ?int $patientId = null, array $extraContext = []): string
    {
        $medicineContext = $this->buildMedicineContext();
        $complaintContext = $this->buildComplaintContext();
        $testContext = $this->buildTestContext();
        $sealContext = $this->buildClinicalSealContext();

        $prompt = "You are a clinical decision support AI assistant working with a licensed physician. ";
        $prompt .= "You have access to the clinic's database. Respond in structured JSON format.\n\n";
        $prompt .= "AVAILABLE MEDICINES IN DATABASE:\n{$medicineContext}\n\n";
        $prompt .= "AVAILABLE COMPLAINTS/SYMPTOMS:\n{$complaintContext}\n\n";
        $prompt .= "AVAILABLE LABORATORY TESTS:\n{$testContext}\n\n";
        $prompt .= "AVAILABLE CLINICAL SEALS:\n{$sealContext}\n\n";

        if ($patientId && $doctorId) {
            $patientContext = $this->buildPatientContext(
                Patient::where('doctor_id', $doctorId)->find($patientId)
            );
            $prompt .= "PATIENT EMR DATA:\n{$patientContext}\n\n";
        }

        $prompt .= "RULES:\n";
        $prompt .= "1. Always recommend medicines that exist in the database when possible.\n";
        $prompt .= "2. Check patient allergies before suggesting any medicine.\n";
        $prompt .= "3. Consider drug interactions when suggesting multiple medicines.\n";
        $prompt .= "4. Consider patient age, weight, gender, and conditions for dosage.\n";
        $prompt .= "5. For pediatric patients, always calculate weight-based dosing.\n";
        $prompt .= "6. Include contraindications and warnings when relevant.\n";
        $prompt .= "7. Always include the disclaimer about physician decision.\n";
        $prompt .= "8. Suggest relevant tests from the available test list.\n";
        $prompt .= "9. Recommend follow-up timing when appropriate.\n";
        $prompt .= "10. Be concise and clinically accurate.\n\n";

        $prompt .= "RESPONSE FORMAT (JSON):\n";
        $prompt .= "{\n";
        $prompt .= '  "reply": "Main clinical response text...",' . "\n";
        $prompt .= '  "suggestions": {' . "\n";
        $prompt .= '    "diagnosis": ["Possible diagnosis 1", "Possible diagnosis 2"],' . "\n";
        $prompt .= '    "medicines": [{"name": "Drug Name", "dosage": "500mg", "frequency": "TDS", "duration": "5 days", "instructions": "After meals"}],' . "\n";
        $prompt .= '    "tests": ["Test 1", "Test 2"],' . "\n";
        $prompt .= '    "advice": ["Advice 1", "Advice 2"],' . "\n";
        $prompt .= '    "follow_up": "Follow up after 1 week"' . "\n";
        $prompt .= "  },\n";
        $prompt .= '  "warnings": ["Warning about allergies or interactions"],' . "\n";
        $prompt .= '  "drug_interactions": [{"drugs": ["Drug1", "Drug2"], "severity": "moderate", "description": "Interaction details"}]' . "\n";
        $prompt .= "}\n\n";

        $prompt .= "IMPORTANT: {$this->disclaimer}\n";
        $prompt .= "If you are not certain about something, say so. Do not fabricate medical information.\n";
        $prompt .= "Always prefer medicines available in the clinic database over external references.\n";

        return $prompt;
    }

    protected function buildUserPrompt(string $message, ?int $patientId = null): string
    {
        return $message;
    }

    protected function buildPatientContext(?Patient $patient): string
    {
        if (!$patient) {
            return 'No patient selected.';
        }

        try {
            return $this->buildPatientContextSafe($patient);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Medical Service: Error building patient context', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);

            $lines = [];
            $lines[] = "Name: {$patient->name}";
            $lines[] = "Gender: " . ($patient->gender ?? 'Unknown');
            if ($patient->date_of_birth) {
                $lines[] = "Age: " . \Carbon\Carbon::parse($patient->date_of_birth)->age . " years";
            }
            if ($patient->blood_group) {
                $lines[] = "Blood Group: {$patient->blood_group}";
            }
            $lines[] = "(Limited context - some data could not be loaded)";
            return implode("\n", $lines);
        }
    }

    protected function buildPatientContextSafe(Patient $patient): string
    {
        $lines = [];
        $lines[] = "Name: {$patient->name}";
        $lines[] = "Gender: " . ($patient->gender ?? 'Unknown');

        if ($patient->date_of_birth) {
            $age = \Carbon\Carbon::parse($patient->date_of_birth)->age;
            $lines[] = "Age: {$age} years";
        }

        if ($patient->blood_group) {
            $lines[] = "Blood Group: {$patient->blood_group}";
        }

        if ($patient->weight) {
            $lines[] = "Weight: {$patient->weight} kg";
        }

        if ($patient->height) {
            $lines[] = "Height: {$patient->height} cm";
        }

        if ($patient->medical_history) {
            $lines[] = "Medical History: {$patient->medical_history}";
        }

        $allergies = $patient->allergies;
        if ($allergies->count() > 0) {
            $allergyList = $allergies->map(function ($a) {
                return "{$a->allergy} (severity: {$a->severity}" .
                    ($a->reaction ? ", reaction: {$a->reaction}" : '') . ")";
            })->implode('; ');
            $lines[] = "ALLERGIES: {$allergyList}";
        }

        $activeConditions = $patient->medicalHistories()->where('status', 'active')->get();
        if ($activeConditions->count() > 0) {
            $conditionsList = $activeConditions->map(function ($c) {
                return $c->condition_name . ($c->diagnosed_date ? ' (since ' . $c->diagnosed_date->format('Y') . ')' : '');
            })->implode('; ');
            $lines[] = "ACTIVE CONDITIONS: {$conditionsList}";
        }

        $diagnoses = $patient->diagnoses()->latest('diagnosed_date')->take(5)->get();
        if ($diagnoses->count() > 0) {
            $diagList = $diagnoses->map(function ($d) {
                return "{$d->diagnosis} ({$d->type}" . ($d->icd_code ? ", ICD: {$d->icd_code}" : '') . ")";
            })->implode('; ');
            $lines[] = "PREVIOUS DIAGNOSES: {$diagList}";
        }

        $prescriptions = $patient->prescriptions()
            ->with(['items.medicine', 'complaints', 'labTests', 'testReports'])
            ->latest()
            ->take(5)
            ->get();

        if ($prescriptions->count() > 0) {
            $lines[] = "\nPRESCRIPTION HISTORY (Last " . $prescriptions->count() . " visits):";
            foreach ($prescriptions as $rx) {
                $date = $rx->created_at->format('Y-m-d');
                $diagnosis = $rx->diagnosis ?: 'No diagnosis';
                $complaints = $rx->complaints->pluck('name')->implode(', ');
                $medicines = $rx->items->where('type', 'medicine')->map(function ($item) {
                    return $item->medicine_name .
                        ($item->dosage ? " {$item->dosage}" : '') .
                        ($item->frequency ? " {$item->frequency}" : '') .
                        ($item->duration ? " {$item->duration}" : '');
                })->implode('; ');
                $tests = $rx->labTests->pluck('test_name')->implode(', ');

                $lines[] = "  Date: {$date} | Diagnosis: {$diagnosis}";
                if ($complaints) $lines[] = "    Complaints: {$complaints}";
                if ($medicines) $lines[] = "    Medicines: {$medicines}";
                if ($tests) $lines[] = "    Tests: {$tests}";
            }
        }

        try {
            $testReports = $patient->testReports()->latest()->take(10)->get();
            if ($testReports->count() > 0) {
                $lines[] = "\nRECENT TEST RESULTS:";
                foreach ($testReports as $report) {
                    $lines[] = "  {$report->test_name}: {$report->parameter_name} = {$report->value} {$report->unit} (ref: {$report->reference_range})";
                }
            }
        } catch (\Exception $e) {
            // Gracefully handle test reports relationship issues
        }

        return implode("\n", $lines);
    }

    protected function buildMedicineContext(): string
    {
        $limit = config('ai.max_context_medicines', 50);

        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->with('category')
            ->take($limit)
            ->get();

        if ($medicines->isEmpty()) {
            return 'No medicines in database.';
        }

        $lines = [];
        foreach ($medicines as $med) {
            $line = "- {$med->name}";
            if ($med->generic_name) $line .= " (Generic: {$med->generic_name})";
            if ($med->category) $line .= " [{$med->category->name}]";
            if ($med->strength) $line .= " | Strength: {$med->strength}";
            if ($med->adult_dose) $line .= " | Adult dose: {$med->adult_dose}";
            if ($med->child_dose) $line .= " | Child dose: {$med->child_dose}";
            if ($med->contraindications) $line .= " | Contraindications: {$med->contraindications}";
            if ($med->side_effects) $line .= " | Side effects: {$med->side_effects}";
            if ($med->drug_interaction_notes) $line .= " | Interactions: {$med->drug_interaction_notes}";
            if ($med->allergy_warning) $line .= " | Allergy warning: {$med->allergy_warning}";
            if ($med->pregnancy_safe !== null) $line .= " | Pregnancy safe: " . ($med->pregnancy_safe ? 'Yes' : 'No');
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    protected function buildComplaintContext(): string
    {
        $limit = config('ai.max_context_complaints', 100);

        $complaints = Complaint::active()
            ->popular()
            ->take($limit)
            ->get()
            ->pluck('name');

        return $complaints->implode(', ') ?: 'No complaints defined.';
    }

    protected function buildTestContext(): string
    {
        $limit = config('ai.max_context_tests', 50);

        $tests = LaboratoryTest::active()
            ->popular()
            ->take($limit)
            ->get()
            ->pluck('test_name');

        return $tests->implode(', ') ?: 'No tests defined.';
    }

    protected function buildClinicalSealContext(): string
    {
        $seals = \App\Models\ClinicalSeal::active()
            ->popular()
            ->take(30)
            ->get()
            ->pluck('name');

        return $seals->implode(', ') ?: 'No clinical seals defined.';
    }

    protected function callOpenAI(string $systemPrompt, string $userMessage): array
    {
        $apiKey = config('ai.openai.api_key');
        $model = config('ai.openai.model', 'gpt-4o-mini');
        $maxTokens = config('ai.openai.max_tokens', 1500);
        $temperature = config('ai.openai.temperature', 0.3);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '');
                return $this->parseAIResponse($content);
            }

            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->fallbackResponse('AI service returned an error. Please try again.');
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', ['message' => $e->getMessage()]);
            return $this->fallbackResponse('AI service is temporarily unavailable. Please try again.');
        }
    }

    protected function parseAIResponse(string $content): array
    {
        $content = trim($content);

        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonStr, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeResponse($decoded);
            }
        }

        return [
            'reply' => $content,
            'suggestions' => [
                'diagnosis' => [],
                'medicines' => [],
                'tests' => [],
                'advice' => [],
                'follow_up' => '',
            ],
            'warnings' => [],
            'drug_interactions' => [],
            'disclaimer' => $this->disclaimer,
        ];
    }

    protected function normalizeResponse(array $data): array
    {
        return [
            'reply' => $data['reply'] ?? $data['message'] ?? 'No response generated.',
            'suggestions' => [
                'diagnosis' => $data['suggestions']['diagnosis'] ?? [],
                'medicines' => $data['suggestions']['medicines'] ?? [],
                'tests' => $data['suggestions']['tests'] ?? [],
                'advice' => $data['suggestions']['advice'] ?? [],
                'follow_up' => $data['suggestions']['follow_up'] ?? '',
            ],
            'warnings' => $data['warnings'] ?? [],
            'drug_interactions' => $data['drug_interactions'] ?? [],
            'disclaimer' => $this->disclaimer,
        ];
    }

    protected function fallbackResponse(string $message): array
    {
        return [
            'reply' => $message,
            'suggestions' => [
                'diagnosis' => [],
                'medicines' => [],
                'tests' => [],
                'advice' => [],
                'follow_up' => '',
            ],
            'warnings' => [],
            'drug_interactions' => [],
            'disclaimer' => $this->disclaimer,
        ];
    }

    protected function smartFallback(string $message, ?int $doctorId = null, ?int $patientId = null, array $extraContext = []): array
    {
        $lowerMessage = strtolower($message);
        $mode = $extraContext['mode'] ?? 'general';

        $patient = null;
        if ($patientId && $doctorId) {
            $patient = Patient::where('doctor_id', $doctorId)->find($patientId);
        }

        if ($mode === 'diagnosis') {
            return $this->fallbackDiagnosis($extraContext['complaints'] ?? [], $patient);
        }

        if ($mode === 'interactions') {
            return $this->fallbackInteractions($extraContext['medicines'] ?? []);
        }

        if ($mode === 'medicines') {
            return $this->fallbackMedicineSuggestion($extraContext['diagnosis'] ?? $message, $patient);
        }

        if ($mode === 'tests') {
            return $this->fallbackTestSuggestion($extraContext['symptoms'] ?? $message, $extraContext['diagnosis'] ?? null, $patient);
        }

        if ($mode === 'analysis') {
            return $this->fallbackPatientAnalysis($patient, $extraContext['patient_context'] ?? '');
        }

        return $this->fallbackGeneralQuery($lowerMessage, $patient, $doctorId);
    }

    protected function fallbackDiagnosis(array $complaintNames, ?Patient $patient): array
    {
        $complaintText = implode(', ', $complaintNames);
        $reply = "Based on the reported complaints ({$complaintText}), here are possible diagnoses to consider:\n\n";

        $diagnosisMap = [
            'headache' => ['Tension headache', 'Migraine', 'Cluster headache', 'Sinusitis'],
            'fever' => ['Viral fever', 'Bacterial infection', 'Dengue', 'Malaria', 'Typhoid'],
            'cough' => ['Upper respiratory infection', 'Bronchitis', 'Asthma', 'Pneumonia', 'TB'],
            'chest pain' => ['Musculoskeletal pain', 'GERD', 'Anxiety', 'Angina', 'Pneumonia'],
            'abdominal pain' => ['Gastritis', 'Appendicitis', 'Cholecystitis', 'IBS', 'Peptic ulcer'],
            'fatigue' => ['Anemia', 'Hypothyroidism', 'Diabetes', 'Depression', 'Sleep disorder'],
            'dizziness' => ['Vertigo', 'Hypotension', 'Anemia', 'Dehydration', 'Inner ear problem'],
            'rash' => ['Dermatitis', 'Allergic reaction', 'Infection', 'Eczema', 'Psoriasis'],
            'joint pain' => ['Osteoarthritis', 'Rheumatoid arthritis', 'Gout', 'Sprain', 'Bursitis'],
            'sore throat' => ['Pharyngitis', 'Tonsillitis', 'Laryngitis', 'Strep throat', 'GERD'],
            'vomiting' => ['Gastroenteritis', 'Food poisoning', 'GERD', 'Appendicitis', 'Migraine'],
            'diarrhea' => ['Gastroenteritis', 'IBS', 'Food poisoning', 'Infection', 'Malabsorption'],
        ];

        $possibleDiagnoses = [];
        foreach ($complaintNames as $complaint) {
            $lowerComplaint = strtolower(trim($complaint));
            foreach ($diagnosisMap as $keyword => $diagnoses) {
                if (str_contains($lowerComplaint, $keyword) || str_contains($keyword, $lowerComplaint)) {
                    $possibleDiagnoses = array_merge($possibleDiagnoses, $diagnoses);
                }
            }
        }

        if (empty($possibleDiagnoses)) {
            $reply .= "Consider the following differential diagnoses based on clinical examination and history.\n";
            $reply .= "Further evaluation and tests may be needed to narrow down the diagnosis.";
        } else {
            $possibleDiagnoses = array_unique($possibleDiagnoses);
            $reply .= "Possible diagnoses:\n";
            foreach (array_slice($possibleDiagnoses, 0, 8) as $i => $diag) {
                $reply .= ($i + 1) . ". {$diag}\n";
            }
        }

        if ($patient) {
            $allergies = $patient->allergies->pluck('allergy')->implode(', ');
            if ($allergies) {
                $reply .= "\nPatient allergies ({$allergies}) should be considered in differential diagnosis.";
            }
        }

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => ['diagnosis' => array_slice($possibleDiagnoses, 0, 6)],
            'warnings' => $this->getAllergyWarnings($patient, []),
        ]);
    }

    protected function fallbackInteractions(array $medicineNames): array
    {
        $knownInteractions = [
            ['Warfarin', 'Ibuprofen', 'moderate', 'Increased bleeding risk. Monitor INR closely.'],
            ['Warfarin', 'Aspirin', 'severe', 'Significant bleeding risk. Avoid combination if possible.'],
            ['Metformin', 'Alcohol', 'severe', 'Risk of lactic acidosis. Avoid alcohol.'],
            ['ACE Inhibitors', 'Potassium', 'moderate', 'Risk of hyperkalemia. Monitor potassium levels.'],
            ['Lithium', 'Ibuprofen', 'moderate', 'Increased lithium levels. Monitor lithium levels.'],
            ['SSRI', 'MAO Inhibitor', 'severe', 'Serotonin syndrome risk. Contraindicated combination.'],
            ['Metronidazole', 'Alcohol', 'severe', 'Disulfiram-like reaction. Avoid alcohol for 48 hours after.'],
            ['Fluoroquinolones', 'Antacids', 'moderate', 'Reduced absorption. Separate by 2 hours.'],
            ['Tetracycline', 'Dairy', 'mild', 'Reduced absorption. Take 2 hours apart.'],
            ['Omeprazole', 'Clopidogrel', 'moderate', 'Reduced antiplatelet effect. Consider alternative PPI.'],
            ['Digoxin', 'Amiodarone', 'severe', 'Increased digoxin levels. Reduce digoxin dose.'],
            ['Methotrexate', 'NSAIDs', 'moderate', 'Increased methotrexate toxicity. Monitor closely.'],
        ];

        $interactions = [];
        $foundMedicines = [];

        foreach ($medicineNames as $name) {
            $lowerName = strtolower($name);
            foreach ($knownInteractions as $interaction) {
                $drug1Lower = strtolower($interaction[0]);
                $drug2Lower = strtolower($interaction[1]);

                if (str_contains($lowerName, $drug1Lower) || str_contains($drug1Lower, $lowerName) ||
                    str_contains($lowerName, $drug2Lower) || str_contains($drug2Lower, $lowerName)) {
                    $foundMedicines[] = $name;
                }
            }
        }

        $checkedPairs = [];
        foreach ($medicineNames as $i => $med1) {
            foreach ($medicineNames as $j => $med2) {
                if ($i >= $j) continue;
                $pair = [$med1, $med2];
                $pairKey = implode(' + ', array_map('strtolower', $pair));
                if (in_array($pairKey, $checkedPairs)) continue;
                $checkedPairs[] = $pairKey;

                foreach ($knownInteractions as $interaction) {
                    $match1 = str_contains(strtolower($med1), strtolower($interaction[0])) ||
                              str_contains(strtolower($interaction[0]), strtolower($med1));
                    $match2 = str_contains(strtolower($med2), strtolower($interaction[1])) ||
                              str_contains(strtolower($interaction[1]), strtolower($med2));
                    $match1rev = str_contains(strtolower($med1), strtolower($interaction[1])) ||
                                 str_contains(strtolower($interaction[1]), strtolower($med1));
                    $match2rev = str_contains(strtolower($med2), strtolower($interaction[0])) ||
                                 str_contains(strtolower($interaction[0]), strtolower($med2));

                    if (($match1 && $match2) || ($match1rev && $match2rev)) {
                        $interactions[] = [
                            'drugs' => [$med1, $med2],
                            'severity' => $interaction[2],
                            'description' => $interaction[3],
                        ];
                    }
                }
            }
        }

        $reply = "Drug Interaction Check for: " . implode(', ', $medicineNames) . "\n\n";

        if (empty($interactions)) {
            $reply .= "No significant known interactions found between these medicines in our database.\n";
            $reply .= "Note: This is not exhaustive. Always verify with updated drug interaction references.";
        } else {
            $reply .= count($interactions) . " interaction(s) found:\n\n";
            foreach ($interactions as $i => $inter) {
                $severity = ucfirst($inter['severity']);
                $reply .= ($i + 1) . ". {$inter['drugs'][0]} + {$inter['drugs'][1]} [{$severity}]\n";
                $reply .= "   {$inter['description']}\n\n";
            }
        }

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => ['medicines' => []],
            'warnings' => array_map(function ($i) {
                return "{$i['drugs'][0]} + {$i['drugs'][1]}: {$i['description']}";
            }, $interactions),
            'drug_interactions' => $interactions,
        ]);
    }

    protected function fallbackMedicineSuggestion(string $diagnosis, ?Patient $patient): array
    {
        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->with('category')
            ->get();

        $lowerDiagnosis = strtolower($diagnosis);
        $allergyNames = [];
        if ($patient) {
            $allergyNames = $patient->allergies->pluck('allergy')->map('strtolower')->toArray();
        }

        $suggestions = [];
        $matchedMeds = [];

        $diagnosisMedicineMap = [
            'fever' => ['paracetamol', 'ibuprofen', 'mefenamic'],
            'headache' => ['paracetamol', 'ibuprofen', 'naproxen'],
            'cough' => ['ambroxol', 'dextromethorphan', 'guaifenesin', 'codeine'],
            'cold' => ['cetirizine', 'loratadine', 'pseudoephedrine'],
            'infection' => ['amoxicillin', 'azithromycin', 'ciprofloxacin', 'metronidazole'],
            'bacteria' => ['amoxicillin', 'cefuroxime', 'azithromycin'],
            'pain' => ['paracetamol', 'ibuprofen', 'diclofenac', 'naproxen'],
            'gastric' => ['omeprazole', 'pantoprazole', 'ranitidine', 'domperidone'],
            'stomach' => ['omeprazole', 'pantoprazole', 'domperidone', 'loperamide'],
            'diarrhea' => ['loperamide', 'metronidazole', 'ornidazole', 'ofloxacin'],
            'vomiting' => ['ondansetron', 'domperidone', 'metoclopramide'],
            'allergy' => ['cetirizine', 'loratadine', 'fexofenadine', 'levocetirizine'],
            'rash' => ['cetirizine', 'hydrocortisone', 'betamethasone'],
            'hypertension' => ['amlodipine', 'losartan', 'enalapril', 'metoprolol'],
            'diabetes' => ['metformin', 'glimepiride', 'sitagliptin'],
            'asthma' => ['salbutamol', 'budesonide', 'montelukast'],
            'arthritis' => ['diclofenac', 'naproxen', 'methotrexate'],
            'depression' => ['sertraline', 'fluoxetine', 'escitalopram'],
            'anxiety' => ['escitalopram', 'alprazolam', 'buspirone'],
            'thyroid' => ['levothyroxine'],
            'anemia' => ['ferrous sulfate', 'folic acid', 'vitamin b12'],
            'inflammation' => ['ibuprofen', 'diclofenac', 'naproxen'],
        ];

        foreach ($diagnosisMedicineMap as $keyword => $drugKeywords) {
            if (str_contains($lowerDiagnosis, $keyword)) {
                $matchedMeds = array_merge($matchedMeds, $drugKeywords);
            }
        }

        if (empty($matchedMeds)) {
            $matchedMeds = ['paracetamol', 'cetirizine'];
        }

        $matchedMeds = array_unique($matchedMeds);

        foreach ($medicines as $med) {
            $medNameLower = strtolower($med->name);
            $genericLower = strtolower($med->generic_name ?? '');

            foreach ($matchedMeds as $match) {
                if (str_contains($medNameLower, $match) || str_contains($genericLower, $match)) {
                    $hasAllergyConflict = false;
                    foreach ($allergyNames as $allergy) {
                        if (str_contains($medNameLower, $allergy) || str_contains($genericLower, $allergy) ||
                            str_contains($med->active_ingredients ?? '', $allergy)) {
                            $hasAllergyConflict = true;
                            break;
                        }
                    }

                    $suggestions[] = [
                        'name' => $med->name,
                        'generic_name' => $med->generic_name,
                        'strength' => $med->strength,
                        'dosage' => $med->adult_dose ?? 'As directed',
                        'frequency' => 'As prescribed',
                        'duration' => $med->duration_recommendation ?? 'As needed',
                        'instructions' => $med->usage_instructions ?? '',
                        'allergy_warning' => $hasAllergyConflict ? 'WARNING: Possible allergy conflict with patient' : '',
                        'contraindications' => $med->contraindications ?? '',
                        'side_effects' => $med->side_effects ?? '',
                    ];
                    break;
                }
            }
        }

        $reply = "Medicine suggestions for: {$diagnosis}\n\n";

        if (!empty($suggestions)) {
            $reply .= "Suggested medicines (from clinic database):\n";
            foreach ($suggestions as $i => $s) {
                $reply .= ($i + 1) . ". {$s['name']}";
                if ($s['generic_name']) $reply .= " ({$s['generic_name']})";
                if ($s['strength']) $reply .= " {$s['strength']}";
                $reply .= "\n   Dosage: {$s['dosage']}";
                if ($s['instructions']) $reply .= " | {$s['instructions']}";
                if ($s['allergy_warning']) $reply .= "\n   ⚠ {$s['allergy_warning']}";
                $reply .= "\n";
            }
        } else {
            $reply .= "No exact matches found in the database. Please search for appropriate medicines manually.\n";
        }

        $warnings = [];
        foreach ($suggestions as $s) {
            if ($s['allergy_warning']) {
                $warnings[] = $s['allergy_warning'];
            }
        }

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => ['medicines' => $suggestions],
            'warnings' => $warnings,
        ]);
    }

    protected function fallbackTestSuggestion(string $symptoms, ?string $diagnosis, ?Patient $patient): array
    {
        $lowerSymptoms = strtolower($symptoms);
        $lowerDiagnosis = strtolower($diagnosis ?? '');

        $symptomTestMap = [
            'fever' => ['CBC', 'ESR', 'CRP', 'Malaria Test', 'Blood Culture'],
            'cough' => ['Chest X-ray', 'CBC', 'Sputum Culture', 'TB Test'],
            'chest pain' => ['ECG', 'Chest X-ray', 'Cardiac Enzymes', 'CBC'],
            'abdominal' => ['CBC', 'Liver Function', 'Renal Function', 'Abdominal Ultrasound'],
            'fatigue' => ['CBC', 'Thyroid Function', 'Blood Sugar', 'Iron Studies'],
            'dizziness' => ['CBC', 'Blood Pressure', 'Blood Sugar', 'Audiometry'],
            'rash' => ['CBC', 'Allergy Test', 'Skin Scraping'],
            'joint pain' => ['CBC', 'ESR', 'CRP', 'Uric Acid', 'RA Factor', 'X-ray'],
            'diarrhea' => ['Stool Culture', 'CBC', 'Electrolytes'],
            'vomiting' => ['CBC', 'Electrolytes', 'Liver Function', 'Abdominal Ultrasound'],
            'weight loss' => ['CBC', 'Thyroid Function', 'Blood Sugar', 'Chest X-ray', 'ESR'],
            'swelling' => ['CBC', 'Renal Function', 'Liver Function', 'Albumin'],
            'blood pressure' => ['ECG', 'Renal Function', 'Lipid Profile', 'Blood Sugar', 'CBC'],
            'sugar' => ['Blood Sugar', 'HbA1c', 'Fasting Insulin', 'Renal Function', 'Lipid Profile'],
            'diabetes' => ['HbA1c', 'Fasting Blood Sugar', 'Post Prandial Sugar', 'Lipid Profile', 'Renal Function', 'Urine Albumin'],
            'liver' => ['LFT', 'HBsAg', 'HCV', 'Ultrasound Abdomen'],
            'kidney' => ['KFT', 'Urinalysis', 'Urine Albumin', 'Ultrasound KUB'],
            'thyroid' => ['TSH', 'Free T3', 'Free T4', 'Thyroid Antibodies'],
            'anemia' => ['CBC', 'Iron Studies', 'Vitamin B12', 'Folic Acid', 'Peripheral Smear'],
            'pregnancy' => ['Urine Pregnancy Test', 'CBC', 'Blood Group', 'HbA1c', 'HIV', 'HBSAG', 'VDRL'],
        ];

        $suggestedTests = [];
        foreach ($symptomTestMap as $keyword => $tests) {
            if (str_contains($lowerSymptoms, $keyword) || str_contains($lowerDiagnosis, $keyword)) {
                $suggestedTests = array_merge($suggestedTests, $tests);
            }
        }

        $suggestedTests = array_unique($suggestedTests);

        if (empty($suggestedTests)) {
            $suggestedTests = ['CBC', 'Blood Sugar', 'ESR'];
        }

        $availableTests = LaboratoryTest::active()->pluck('test_name')->map('strtolower')->toArray();

        $reply = "Suggested tests for: {$symptoms}";
        if ($diagnosis) $reply .= " (Diagnosis: {$diagnosis})";
        $reply .= "\n\n";
        $reply .= "Recommended investigations:\n";
        foreach ($suggestedTests as $i => $test) {
            $reply .= ($i + 1) . ". {$test}\n";
        }

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => ['tests' => $suggestedTests],
            'warnings' => [],
        ]);
    }

    protected function fallbackPatientAnalysis(?Patient $patient, string $context): array
    {
        if (!$patient) {
            return $this->fallbackResponse('Patient not found.');
        }

        $prescriptionCount = $patient->prescriptions()->count();
        $allergyCount = $patient->allergies()->count();
        $activeConditions = $patient->medicalHistories()->where('status', 'active')->count();
        $diagnosesCount = $patient->diagnoses()->count();

        $reply = "Patient Analysis: {$patient->name}\n\n";
        $reply .= "Summary:\n";
        $reply .= "- Total prescriptions: {$prescriptionCount}\n";
        $reply .= "- Recorded allergies: {$allergyCount}\n";
        $reply .= "- Active conditions: {$activeConditions}\n";
        $reply .= "- Recorded diagnoses: {$diagnosesCount}\n";

        if ($allergyCount > 0) {
            $allergies = $patient->allergies->pluck('allergy', 'severity')->toArray();
            $reply .= "\nAllergy Profile:\n";
            foreach ($patient->allergies as $a) {
                $reply .= "- {$a->allergy} ({$a->severity})";
                if ($a->reaction) $reply .= " - Reaction: {$a->reaction}";
                $reply .= "\n";
            }
        }

        if ($activeConditions > 0) {
            $reply .= "\nActive Conditions:\n";
            foreach ($patient->medicalHistories()->where('status', 'active')->get() as $c) {
                $reply .= "- {$c->condition_name}";
                if ($c->diagnosed_date) $reply .= " (since {$c->diagnosed_date->format('Y')})";
                $reply .= "\n";
            }
        }

        $lastPrescription = $patient->prescriptions()->latest()->first();
        if ($lastPrescription) {
            $reply .= "\nLast Visit: {$lastPrescription->created_at->format('M d, Y')}";
            if ($lastPrescription->diagnosis) {
                $reply .= " | Diagnosis: {$lastPrescription->diagnosis}";
            }
        }

        $reply .= "\n\nRecommendations:\n";
        $reply .= "1. Review and update medication list regularly\n";
        $reply .= "2. Ensure all allergies are up to date\n";
        if ($prescriptionCount > 3) {
            $reply .= "3. Consider comprehensive treatment review\n";
        }
        $reply .= "3. Schedule follow-up as clinically indicated\n";

        $recommendations = [
            'Schedule regular follow-up appointments',
            'Review medication adherence',
            'Update allergy records if needed',
            'Monitor active conditions',
        ];

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => [
                'advice' => $recommendations,
            ],
            'warnings' => $this->getAllergyWarnings($patient, []),
        ]);
    }

    protected function fallbackGeneralQuery(string $message, ?Patient $patient, ?int $doctorId): array
    {
        $responses = [
            'headache' => 'For headaches, consider Paracetamol (500-1000mg) or Ibuprofen (200-400mg). Assess for underlying causes: tension, migraine, sinusitis, or hypertension. If chronic or severe, consider neurology referral.',
            'fever' => 'For fever: Paracetamol (10-15mg/kg/dose in children, 500-1000mg adults) every 4-6 hours. Encourage fluids and rest. If fever persists >3 days or exceeds 39.4C, investigate further. Consider CBC, ESR, CRP.',
            'cough' => 'For cough evaluation: Determine if dry or productive. Dry cough: Dextromethorphan or Codeine-based. Productive: Expectorants (Guaifenesin, Ambroxol). If >2 weeks, consider chest X-ray and TB evaluation.',
            'bp' => 'For hypertension: First-line - Amlodipine (5-10mg), Losartan (50-100mg), or Enalapril (5-20mg). Lifestyle: Reduce sodium, exercise, weight management, stress reduction. Monitor regularly.',
            'diabetes' => 'For diabetes management: Metformin (500-2000mg/day) first-line for T2DM. Consider HbA1c targets. Add sulfonylureas, DPP-4 inhibitors, or insulin as needed. Diet and exercise essential.',
            'antibiotic' => 'Antibiotics only for confirmed bacterial infections. Consider: Amoxicillin (500mg TDS), Azithromycin (500mg OD), Ciprofloxacin (500mg BD). Always consider culture sensitivity.',
            'stomach' => 'For GI complaints: Antacids for dyspepsia, Omeprazole (20mg OD) for GERD, Domperidone (10mg TDS) for nausea. Evaluate for H. pylori if persistent.',
            'pain' => 'Follow WHO analgesic ladder: Mild - Paracetamol/NSAIDs. Moderate - Codeine/Tramadol combinations. Severe - Opioids under supervision. Address underlying cause.',
            'allergy' => 'For allergic reactions: Antihistamines - Cetirizine (10mg OD) or Loratadine (10mg OD). Severe cases: Corticosteroids. Identify and avoid triggers.',
            'infection' => 'Assess infection site and severity. Consider: Amoxicillin-Clavulanate (625mg TDS), Ciprofloxacin (500mg BD), Metronidazole (400mg TDS). Culture before empirical therapy when possible.',
        ];

        $matched = null;
        foreach ($responses as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                $matched = $response;
                break;
            }
        }

        if (!$matched) {
            $reply = "I can help with medical queries. You can ask about:\n";
            $reply .= "- Specific conditions (headache, fever, cough, etc.)\n";
            $reply .= "- Drug interactions\n";
            $reply .= "- Patient analysis\n";
            $reply .= "- Medicine suggestions\n";
            $reply .= "- Test recommendations\n\n";
            $reply .= "For more detailed assistance, use the quick action buttons or select a patient first.";
        } else {
            $reply = $matched;
        }

        if ($patient) {
            $reply .= "\n\nNote: You have {$patient->name} selected. I can provide patient-specific advice if needed.";
        }

        return $this->normalizeResponse([
            'reply' => $reply,
            'suggestions' => [],
            'warnings' => $this->getAllergyWarnings($patient, []),
        ]);
    }

    protected function getAllergyWarnings(?Patient $patient, array $medicineNames): array
    {
        if (!$patient) return [];

        $allergies = $patient->allergies->pluck('allergy')->map('strtolower')->toArray();
        if (empty($allergies)) return [];

        $warnings = [];
        foreach ($allergies as $allergy) {
            $warnings[] = "Patient has documented allergy to: {$allergy}. Avoid prescribing related medications.";
        }

        return $warnings;
    }
}
