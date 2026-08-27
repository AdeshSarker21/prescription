<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorFeatureSetting;
use App\Models\Advice;
use App\Models\ClinicalSeal;
use App\Models\Complaint;
use App\Models\InvestigationGroup;
use App\Models\LaboratoryTest;
use App\Models\Medicine;
use App\Models\MedicineSuggestion;
use App\Models\MedicalHistoryCondition;
use App\Models\Patient;
use App\Models\PatientMedicalHistory;
use App\Models\Prescription;
use App\Models\PrescriptionAdvice;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status');

        $prescriptions = Prescription::where('doctor_id', auth()->id())
            ->with('patient')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('patient', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhere('prescription_number', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('doctor.prescriptions.index', compact('prescriptions', 'statusFilter'));
    }

    public function create(Request $request): View
    {
        $doctorId = auth()->id();
        $selectedPatient = $request->get('patient_id');

        $patients = Patient::where('doctor_id', $doctorId)->get();
        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->select('id', 'name', 'brand_name', 'strength', 'generic_name')
            ->get();
        $investigationGroups = InvestigationGroup::with('parameters')->orderBy('sort_order')->get();

        // Get doctor feature settings
        $featureSetting = DoctorFeatureSetting::getForDoctor($doctorId);

        // Pre-load last recorded weight/height per patient for historical comparison
        $patientIds = $patients->pluck('id');
        $lastVitals = Prescription::whereIn('patient_id', $patientIds)
            ->where('doctor_id', $doctorId)
            ->whereNotNull('weight')
            ->selectRaw('patient_id, weight, height, created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('patient_id');

        $previousWeightByPatient = [];
        $previousHeightByPatient = [];
        foreach ($lastVitals as $vital) {
            $previousWeightByPatient[$vital->patient_id] = $vital->weight;
            $previousHeightByPatient[$vital->patient_id] = $vital->height;
        }

        // Pre-load last 5 prescriptions per patient for history feature
        $previousPrescriptionsJson = collect();
        if ($patientIds->isNotEmpty()) {
            // Step 1: Get only the latest 5 prescription IDs per patient (lightweight)
            $latestRxIds = Prescription::whereIn('patient_id', $patientIds)
                ->where('doctor_id', $doctorId)
                ->select('id', 'patient_id')
                ->latest()
                ->get()
                ->groupBy('patient_id')
                ->map(fn($group) => $group->take(5)->pluck('id'))
                ->flatten()
                ->toArray();

            if (!empty($latestRxIds)) {
                // Step 2: Load only those prescriptions with eager-loaded relations
                $previousPrescriptions = Prescription::whereIn('id', $latestRxIds)
                    ->with(['items', 'complaints', 'tests', 'advice', 'testReports', 'testReportResults'])
                    ->get()
                    ->groupBy('patient_id');

                $previousPrescriptionsJson = $previousPrescriptions->map(function ($prescriptions, $patientId) {
                    return [
                        'patient_id' => (int) $patientId,
                        'list' => $prescriptions->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'number' => $p->prescription_number,
                                'date' => $p->created_at->format('d M Y'),
                                'status' => $p->status,
                                'diagnosis' => $p->diagnosis,
                                'complaints' => $p->complaints->map(fn($c) => ['name' => $c->name, 'notes' => $c->pivot?->notes ?? '']),
                                'tests' => $p->tests->map(fn($t) => ['name' => $t->test_name]),
                                'items' => $p->items->map(fn($i) => [
                                    'type' => $i->type,
                                    'medicine_name' => $i->medicine_name,
                                    'medicine_id' => $i->medicine_id,
                                    'strength' => $i->dosage,
                                    'frequency' => $i->frequency,
                                    'duration' => $i->duration,
                                    'instructions' => $i->instructions,
                                    'seal_id' => $i->seal_id,
                                    'seal_text' => $i->seal_text,
                                    'seal_details' => $i->seal_details,
                                    'sort_order' => $i->sort_order,
                                ]),
                                'advice' => $p->advice->pluck('advice'),
                                'follow_up_date' => $p->follow_up_date?->format('Y-m-d'),
                                'follow_up_instructions' => $p->follow_up_instructions,
                                'bp_systolic' => $p->bp_systolic,
                                'bp_diastolic' => $p->bp_diastolic,
                                'pulse_rate' => $p->pulse_rate,
                                'spo2' => $p->spo2,
                                'weight' => $p->weight,
                                'height' => $p->height,
                                'testReports' => $p->testReports->groupBy('test_name')->map(fn($params) => $params->map(fn($r) => [
                                    'parameter' => $r->parameter_name,
                                    'value' => $r->value ?? '',
                                    'unit' => $r->unit ?? '',
                                    'reference_range' => $r->reference_range ?? '',
                                ])),
                                'testReportResults' => $p->testReportResults->map(fn($r) => [
                                    'test_name' => $r->test_name,
                                    'result' => $r->result ?? '',
                                ]),
                            ];
                        }),
                    ];
                })->values();
            }
        }

        return view('doctor.prescriptions.create', compact(
            'patients', 'medicines', 'investigationGroups',
            'previousWeightByPatient', 'previousHeightByPatient', 'previousPrescriptionsJson', 'selectedPatient', 'featureSetting'
        ));
    }

    public function getPatientPrescriptions(Patient $patient): JsonResponse
    {
        $prescriptions = Prescription::where('patient_id', $patient->id)
            ->where('doctor_id', auth()->id())
            ->with(['items', 'complaints', 'tests', 'advice', 'testReports', 'testReportResults'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'prescription_number' => $p->prescription_number,
                    'date' => $p->created_at->format('d M Y'),
                    'status' => $p->status,
                    'diagnosis' => $p->diagnosis,
                    'follow_up_date' => $p->follow_up_date?->format('Y-m-d'),
                    'follow_up_instructions' => $p->follow_up_instructions,
                    'complaints' => $p->complaints->map(fn($c) => [
                        'name' => $c->name,
                        'notes' => $c->pivot?->notes ?? '',
                    ]),
                    'tests' => $p->tests->map(fn($t) => ['name' => $t->test_name]),
                    'items' => $p->items->map(fn($i) => [
                        'type' => $i->type,
                        'medicine_name' => $i->medicine_name,
                        'medicine_id' => $i->medicine_id,
                        'strength' => $i->dosage,
                        'frequency' => $i->frequency,
                        'duration' => $i->duration,
                        'instructions' => $i->instructions,
                        'seal_id' => $i->seal_id,
                        'seal_text' => $i->seal_text,
                        'seal_details' => $i->seal_details,
                        'sort_order' => $i->sort_order,
                    ]),
                    'advice' => $p->advice->pluck('name'),
                    'bp_systolic' => $p->bp_systolic,
                    'bp_diastolic' => $p->bp_diastolic,
                    'pulse_rate' => $p->pulse_rate,
                    'spo2' => $p->spo2,
                    'weight' => $p->weight,
                    'height' => $p->height,
                    'testReports' => $p->testReports->groupBy('test_name')->map(fn($params) => $params->map(fn($r) => [
                        'parameter' => $r->parameter_name,
                        'value' => $r->value ?? '',
                        'unit' => $r->unit ?? '',
                        'reference_range' => $r->reference_range ?? '',
                    ])),
                    'testReportResults' => $p->testReportResults->map(fn($r) => [
                        'test_name' => $r->test_name,
                        'result' => $r->result ?? '',
                    ]),
                ];
            });

        return response()->json($prescriptions);
    }

    public function getPatientData(Patient $patient): \Illuminate\Http\JsonResponse
    {
        $previous = Prescription::where('patient_id', $patient->id)
            ->where('doctor_id', auth()->id())
            ->whereNotNull('weight')
            ->latest()
            ->first();

        return response()->json([
            'name' => $patient->name,
            'phone' => $patient->phone,
            'address' => $patient->address,
            'gender' => $patient->gender,
            'age' => $patient->date_of_birth ? $patient->date_of_birth->age : null,
            'previous_weight' => $previous?->weight,
            'previous_height' => $previous?->height,
        ]);
    }

    public function getPatientMedicalHistories(Patient $patient): \Illuminate\Http\JsonResponse
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $histories = PatientMedicalHistory::where('patient_id', $patient->id)
            ->orderBy('condition_name')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'condition' => $h->condition_name,
                'status' => $h->status ?? 'active',
                'notes' => $h->notes,
                'date' => $h->diagnosed_date ? \Carbon\Carbon::parse($h->diagnosed_date)->format('M Y') : null,
            ]);

        return response()->json($histories);
    }

    public function storePatientMedicalHistory(Request $request, Patient $patient): \Illuminate\Http\JsonResponse
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'condition_name' => 'required|string|max:255',
            'diagnosed_date' => 'nullable|date',
            'status' => 'required|in:active,resolved',
            'notes' => 'nullable|string',
        ]);

        $history = $patient->medicalHistories()->create($data);

        return response()->json([
            'success' => true,
            'history' => [
                'id' => $history->id,
                'condition' => $history->condition_name,
                'status' => $history->status,
                'notes' => $history->notes,
                'date' => $history->diagnosed_date ? \Carbon\Carbon::parse($history->diagnosed_date)->format('M Y') : null,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'bp_systolic' => 'nullable|integer|min:0|max:300',
            'bp_diastolic' => 'nullable|integer|min:0|max:300',
            'pulse_rate' => 'nullable|integer|min:0|max:300',
            'spo2' => 'nullable|numeric|min:0|max:100',
            'items' => 'nullable|array',
            'items.*.medicine_id' => 'nullable|integer|exists:medicines,id',
            'items.*.medicine_name' => 'nullable|string',
            'items.*.strength' => 'nullable|string',
            'items.*.frequency' => 'nullable|string',
            'items.*.duration' => 'nullable|string',
            'items.*.instructions' => 'nullable|string',
            'items.*.seal_id' => 'nullable|integer|exists:clinical_seals,id',
            'items.*.seal_text' => 'nullable|string',
            'items.*.seal_details' => 'nullable|string',
            'advice' => 'nullable|array',
            'advice.*' => 'nullable|string|max:255',
            'complaints_json' => 'nullable|string',
            'tests_json' => 'nullable|string',
            'test_reports_json' => 'nullable|string',
            'test_results_json' => 'nullable|string',
            'advice_json' => 'nullable|string',
            'medical_histories_json' => 'nullable|string',
            'seals_json' => 'nullable|string',
            'family_history_data' => 'nullable|string',
            'menstrual_history_data' => 'nullable|string',
            'drug_history_data' => 'nullable|string',
            'ot_note_data' => 'nullable|string',
            'anesthesia_data' => 'nullable|string',
        ]);

        // Validate no duplicate seal IDs in items and seals_json
        $usedSealIds = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['seal_id']) && $item['type'] !== 'medicine') {
                    if (in_array($item['seal_id'], $usedSealIds)) {
                        return back()->withErrors(['items' => 'Duplicate Clinical Seal found. Each seal can only be added once per prescription.'])->withInput();
                    }
                    $usedSealIds[] = $item['seal_id'];
                }
            }
        }
        if (!empty($data['seals_json'])) {
            $seals = json_decode($data['seals_json'], true);
            if (is_array($seals)) {
                foreach ($seals as $seal) {
                    if (!empty($seal['seal_id'])) {
                        if (in_array($seal['seal_id'], $usedSealIds)) {
                            return back()->withErrors(['seals_json' => 'Duplicate Clinical Seal found. Each seal can only be added once per prescription.'])->withInput();
                        }
                        $usedSealIds[] = $seal['seal_id'];
                    }
                }
            }
        }

        $data['doctor_id'] = auth()->id();
        $data['prescription_number'] = 'RX-' . date('Ymd') . '-' . str_pad(Prescription::count() + 1, 4, '0', STR_PAD_LEFT);

        $hasMedicines = !empty($data['items']) && collect($data['items'])->filter(fn($i) => !empty($i['medicine_name']))->count() > 0;
        $hasTestResults = !empty($data['test_results_json']) || !empty($data['test_reports_json']);
        $hasSeals = !empty($data['seals_json']);

        if ($hasMedicines || $hasSeals) {
            $data['status'] = Prescription::STATUS_TREATMENT_STARTED;
            $data['treatment_started_at'] = now();
            $data['investigation_pending'] = false;
        } elseif ($hasTestResults) {
            $data['status'] = Prescription::STATUS_REPORT_RECEIVED;
            $data['investigation_pending'] = false;
            $data['report_received_at'] = now();
        } else {
            $data['status'] = Prescription::STATUS_INVESTIGATION_PENDING;
            $data['investigation_pending'] = true;
            $data['treatment_started_at'] = null;
        }

        $prescription = Prescription::create($data);

        // Log initial status
        $prescription->statusLogs()->create([
            'old_status' => null,
            'new_status' => $prescription->status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        if (!empty($data['items'])) {
        $sortOrder = 0;
        foreach ($data['items'] as $item) {
            if (empty($item['medicine_name'])) {
                continue;
            }

            $suggestionId = null;

            if (empty($item['medicine_id']) && !empty($item['medicine_name'])) {
                $normalizedName = mb_strtolower(trim($item['medicine_name']));
                $existingSuggestion = MedicineSuggestion::whereRaw('LOWER(name) = ?', [$normalizedName])
                    ->where('doctor_id', auth()->id())
                    ->where('status', '!=', MedicineSuggestion::STATUS_REJECTED)
                    ->first();

                if (!$existingSuggestion) {
                    $suggestion = MedicineSuggestion::create([
                        'name' => trim($item['medicine_name']),
                        'strength' => $item['strength'] ?? null,
                        'doctor_id' => auth()->id(),
                        'status' => MedicineSuggestion::STATUS_PENDING,
                        'reason' => 'Auto-suggested from prescription (missing medicine)',
                    ]);
                    $suggestionId = $suggestion->id;
                } else {
                    $suggestionId = $existingSuggestion->id;
                }
            }

            $prescription->items()->create([
                'medicine_id' => $item['medicine_id'] ?? null,
                'medicine_name' => $item['medicine_name'],
                'dosage' => $item['strength'] ?? null,
                'frequency' => $item['frequency'] ?? null,
                'duration' => $item['duration'] ?? null,
                'instructions' => $item['instructions'] ?? null,
                'seal_id' => $item['seal_id'] ?? null,
                'seal_text' => $item['seal_text'] ?? null,
                'seal_details' => $item['seal_details'] ?? null,
                'medicine_suggestion_id' => $suggestionId,
                'type' => 'medicine',
                'sort_order' => $sortOrder++,
            ]);
        }
        }

        if (!empty($data['seals_json'])) {
            $seals = json_decode($data['seals_json'], true);
            if (is_array($seals)) {
                foreach ($seals as $seal) {
                    $sealId = $seal['seal_id'] ?? null;
                    $sealText = $seal['seal_text'] ?? ($seal['name'] ?? '');
                    $sealDetails = $seal['seal_details'] ?? null;
                    $position = $seal['position'] ?? null;
                    $duration = $seal['duration'] ?? null;

                    if (empty($sealText)) continue;

                    if ($sealId) {
                        $sealModel = ClinicalSeal::find($sealId);
                        if ($sealModel) {
                            $sealModel->increment('used_count');
                        }
                    } elseif (!empty($sealText)) {
                        $sealModel = ClinicalSeal::findByNameOrCreate($sealText, auth()->id());
                        $sealModel->increment('used_count');
                        $sealId = $sealModel->id;
                    }

                    $prescription->items()->create([
                        'type' => 'seal',
                        'seal_id' => $sealId,
                        'seal_text' => $sealText,
                        'seal_details' => $sealDetails,
                        'medicine_name' => $sealText,
                        'duration' => $duration,
                        'sort_order' => $position ?? $sortOrder++,
                    ]);
                }
            }
        }

        if (!empty($data['advice'])) {
            foreach (array_filter($data['advice']) as $i => $adviceText) {
                $prescription->advice()->create([
                    'advice' => $adviceText,
                    'sort_order' => $i,
                ]);
            }
        }

        if (!empty($data['complaints_json'])) {
            $complaints = json_decode($data['complaints_json'], true);
            if (is_array($complaints)) {
                $syncData = [];
                foreach ($complaints as $i => $complaint) {
                    if (!empty($complaint['id'])) {
                        $c = Complaint::find($complaint['id']);
                        if ($c) {
                            $c->increment('used_count');
                            $syncData[$c->id] = [
                                'notes' => $complaint['notes'] ?? null,
                                'sort_order' => $i,
                            ];
                        }
                    } elseif (!empty($complaint['name'])) {
                        $c = Complaint::findByNameOrCreate($complaint['name'], auth()->id());
                        $c->increment('used_count');
                        $syncData[$c->id] = [
                            'notes' => $complaint['notes'] ?? null,
                            'sort_order' => $i,
                        ];
                    }
                }
                $prescription->complaints()->sync($syncData);
            }
        }

        if (!empty($data['tests_json'])) {
            $tests = json_decode($data['tests_json'], true);
            if (is_array($tests)) {
                foreach ($tests as $i => $test) {
                    if (!empty($test['id'])) {
                        $lt = LaboratoryTest::find($test['id']);
                        if ($lt) {
                            $lt->increment('used_count');
                            $prescription->tests()->create([
                                'laboratory_test_id' => $lt->id,
                                'test_name' => $lt->test_name,
                                'sort_order' => $i,
                            ]);
                        }
                    } elseif (!empty($test['name'])) {
                        $lt = LaboratoryTest::findByNameOrCreate($test['name'], auth()->id());
                        $lt->increment('used_count');
                        $prescription->tests()->create([
                            'laboratory_test_id' => $lt->id,
                            'test_name' => $lt->test_name,
                            'sort_order' => $i,
                        ]);
                    }
                }
            }
        }

        if (!empty($data['advice_json'])) {
            $advices = json_decode($data['advice_json'], true);
            if (is_array($advices)) {
                $adviceIds = [];
                foreach ($advices as $advice) {
                    if (!empty($advice['id'])) {
                        $a = Advice::find($advice['id']);
                        if ($a) {
                            $a->increment('used_count');
                            $adviceIds[] = $a->id;
                        }
                    } elseif (!empty($advice['name'])) {
                        $a = Advice::findByNameOrCreate($advice['name'], auth()->id());
                        $a->increment('used_count');
                        $adviceIds[] = $a->id;
                    }
                }
                if (!empty($adviceIds)) {
                    $prescription->advices()->sync($adviceIds);
                }
            }
        } elseif (!empty($data['advice'])) {
            foreach (array_filter($data['advice']) as $i => $adviceText) {
                $a = Advice::findByNameOrCreate($adviceText, auth()->id());
                $a->increment('used_count');
                $prescription->advices()->attach($a->id);
            }
        }

        if (!empty($data['medical_histories_json'])) {
            $histories = json_decode($data['medical_histories_json'], true);
            if (is_array($histories) && $prescription->patient_id) {
                foreach ($histories as $history) {
                    if (!empty($history['id'])) {
                        $mhc = MedicalHistoryCondition::find($history['id']);
                        if ($mhc) {
                            $mhc->increment('used_count');
                            $prescription->patient->medicalHistories()->updateOrCreate(
                                ['medical_history_condition_id' => $mhc->id],
                                [
                                    'condition_name' => $mhc->name,
                                    'status' => 'active',
                                ]
                            );
                        }
                    } elseif (!empty($history['name'])) {
                        $mhc = MedicalHistoryCondition::findByNameOrCreate($history['name'], auth()->id());
                        $mhc->increment('used_count');
                        $prescription->patient->medicalHistories()->updateOrCreate(
                            ['medical_history_condition_id' => $mhc->id],
                            [
                                'condition_name' => $mhc->name,
                                'status' => 'active',
                            ]
                        );
                    }
                }
            }
        }

        if (!empty($data['test_results_json'])) {
            $results = json_decode($data['test_results_json'], true);
            if (is_array($results)) {
                foreach ($results as $r) {
                    if (!empty($r['test_name'])) {
                        $prescription->testReportResults()->create([
                            'test_name' => $r['test_name'],
                            'result' => $r['result'] ?? null,
                        ]);
                    }
                }
            }
        } elseif (!empty($data['test_reports_json'])) {
            $reports = json_decode($data['test_reports_json'], true);
            if (is_array($reports)) {
                $sortOrder = 0;
                foreach ($reports as $testName => $parameters) {
                    if (is_array($parameters)) {
                        foreach ($parameters as $param) {
                            if (!empty($param['parameter'])) {
                                $prescription->testReports()->create([
                                    'test_name' => $testName,
                                    'parameter_name' => $param['parameter'],
                                    'value' => $param['value'] ?? null,
                                    'unit' => $param['unit'] ?? null,
                                    'reference_range' => $param['reference_range'] ?? null,
                                    'sort_order' => $sortOrder++,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Save feature data
        if (!empty($data['family_history_data'])) {
            $prescription->update(['family_history_data' => json_decode($data['family_history_data'], true)]);
        }
        if (!empty($data['menstrual_history_data'])) {
            $prescription->update(['menstrual_history_data' => json_decode($data['menstrual_history_data'], true)]);
        }
        if (!empty($data['drug_history_data'])) {
            $prescription->update(['drug_history_data' => json_decode($data['drug_history_data'], true)]);
        }
        if (!empty($data['ot_note_data'])) {
            $prescription->update(['ot_note_data' => json_decode($data['ot_note_data'], true)]);
        }
        if (!empty($data['anesthesia_data'])) {
            $prescription->update(['anesthesia_data' => json_decode($data['anesthesia_data'], true)]);
        }

        if ($request->has('_print') || $request->input('action') === 'print') {
            return redirect()->route('doctor.prescriptions.print', $prescription);
        }

        if ($request->has('_add_medicines_later')) {
            return redirect()->route('doctor.prescriptions.show', $prescription)
                ->with('success', 'Prescription saved. You can add medicines later after reviewing test reports.');
        }

        return redirect()->route('doctor.prescriptions.index')
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription): View
    {
        // if ($prescription->doctor_id !== auth()->id()) {
        //     abort(403);
        // }

        $prescription->load([
            'patient',
            'items.medicine',
            'advice',
            'advices',
            'complaints',
            'tests',
            'testReports',
            'testReportResults',
            'statusLogs.changedBy',
        ]);

        $doctorSetting = \App\Models\DoctorPrescriptionSetting::firstOrCreate(['doctor_id' => $prescription->doctor_id]);
        $customHeader = null;
        $customFooter = null;

        if ($doctorSetting) {
            if ($doctorSetting->header_enabled && $doctorSetting->header_id) {
                $customHeader = \App\Models\PrescriptionHeader::find($doctorSetting->header_id);
            }
            if ($doctorSetting->footer_enabled && $doctorSetting->footer_id) {
                $customFooter = \App\Models\PrescriptionFooter::find($doctorSetting->footer_id);
            }
        }

        return view('doctor.prescriptions.show', compact('prescription', 'customHeader', 'customFooter', 'doctorSetting'));
    }

    public function edit(Prescription $prescription): View
    {
        // if ($prescription->doctor_id !== auth()->id()) {
        //     abort(403);
        // }

        if (!$prescription->isEditable()) {
            return redirect()->route('doctor.prescriptions.show', $prescription)
                ->with('error', 'Completed prescriptions cannot be edited. Reopen it first to make changes.');
        }

        $doctorId = auth()->id();
        $patients = Patient::where('doctor_id', $doctorId)->get();
        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->select('id', 'name', 'brand_name', 'strength', 'generic_name')
            ->get();
        $investigationGroups = InvestigationGroup::with('parameters')->orderBy('sort_order')->get();
        $prescription->load(['items.seal', 'advice', 'complaints', 'tests', 'testReports']);

        // Get doctor feature settings
        $featureSetting = DoctorFeatureSetting::getForDoctor($doctorId);

        $patientIds = $patients->pluck('id');
        $lastVitals = Prescription::whereIn('patient_id', $patientIds)
            ->where('doctor_id', $doctorId)
            ->whereNotNull('weight')
            ->selectRaw('patient_id, weight, height, created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('patient_id');

        $previousWeightByPatient = [];
        $previousHeightByPatient = [];
        foreach ($lastVitals as $vital) {
            $previousWeightByPatient[$vital->patient_id] = $vital->weight;
            $previousHeightByPatient[$vital->patient_id] = $vital->height;
        }

        return view('doctor.prescriptions.edit', compact('prescription', 'patients', 'medicines', 'investigationGroups', 'previousWeightByPatient', 'previousHeightByPatient', 'featureSetting'));
    }

    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        // if ($prescription->doctor_id !== auth()->id()) {
        //     abort(403);
        // }

        if (!$prescription->isEditable()) {
            return redirect()->route('doctor.prescriptions.show', $prescription)
                ->with('error', 'Completed prescriptions cannot be edited.');
        }

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'bp_systolic' => 'nullable|integer|min:0|max:300',
            'bp_diastolic' => 'nullable|integer|min:0|max:300',
            'pulse_rate' => 'nullable|integer|min:0|max:300',
            'spo2' => 'nullable|numeric|min:0|max:100',
            'items' => 'nullable|array',
            'items.*.medicine_id' => 'nullable|integer|exists:medicines,id',
            'items.*.medicine_name' => 'nullable|string',
            'items.*.strength' => 'nullable|string',
            'items.*.frequency' => 'nullable|string',
            'items.*.duration' => 'nullable|string',
            'items.*.instructions' => 'nullable|string',
            'items.*.seal_id' => 'nullable|integer|exists:clinical_seals,id',
            'items.*.seal_text' => 'nullable|string',
            'items.*.seal_details' => 'nullable|string',
            'advice' => 'nullable|array',
            'advice.*' => 'nullable|string|max:255',
            'complaints_json' => 'nullable|string',
            'tests_json' => 'nullable|string',
            'test_reports_json' => 'nullable|string',
            'test_results_json' => 'nullable|string',
            'advice_json' => 'nullable|string',
            'medical_histories_json' => 'nullable|string',
            'seals_json' => 'nullable|string',
            'family_history_data' => 'nullable|string',
            'menstrual_history_data' => 'nullable|string',
            'drug_history_data' => 'nullable|string',
            'ot_note_data' => 'nullable|string',
            'anesthesia_data' => 'nullable|string',
        ]);

        // Validate no duplicate seal IDs in items and seals_json
        $usedSealIds = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (!empty($item['seal_id']) && ($item['type'] ?? 'medicine') !== 'medicine') {
                    if (in_array($item['seal_id'], $usedSealIds)) {
                        return back()->withErrors(['items' => 'Duplicate Clinical Seal found. Each seal can only be added once per prescription.'])->withInput();
                    }
                    $usedSealIds[] = $item['seal_id'];
                }
            }
        }
        if (!empty($data['seals_json'])) {
            $seals = json_decode($data['seals_json'], true);
            if (is_array($seals)) {
                foreach ($seals as $seal) {
                    if (!empty($seal['seal_id'])) {
                        if (in_array($seal['seal_id'], $usedSealIds)) {
                            return back()->withErrors(['seals_json' => 'Duplicate Clinical Seal found. Each seal can only be added once per prescription.'])->withInput();
                        }
                        $usedSealIds[] = $seal['seal_id'];
                    }
                }
            }
        }

        $hasMedicines = !empty($data['items']) && collect($data['items'])->filter(fn($i) => !empty($i['medicine_name']))->count() > 0;
        $hasTestResults = !empty($data['test_results_json']) || !empty($data['test_reports_json']);
        $hasSeals = !empty($data['seals_json']);
        $manualStatus = $request->input('status');
        $followUpDateSet = !empty($data['follow_up_date']);

        $updateData = [
            'patient_id' => $data['patient_id'],
            'diagnosis' => $data['diagnosis'] ?? null,
            'notes' => $data['notes'] ?? null,
            'follow_up_instructions' => $data['follow_up_instructions'] ?? null,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'bp_systolic' => $data['bp_systolic'] ?? null,
            'bp_diastolic' => $data['bp_diastolic'] ?? null,
            'pulse_rate' => $data['pulse_rate'] ?? null,
            'spo2' => $data['spo2'] ?? null,
        ];

        $newStatus = $manualStatus && in_array($manualStatus, Prescription::STATUSES)
            ? $manualStatus
            : null;

        if (!$newStatus) {
            if ($followUpDateSet && !in_array($prescription->status, [
                Prescription::STATUS_INVESTIGATION_PENDING,
                Prescription::STATUS_COMPLETED,
            ])) {
                $newStatus = Prescription::STATUS_FOLLOW_UP;
                $updateData['investigation_pending'] = false;
            } elseif ($hasMedicines || $hasSeals) {
                $newStatus = Prescription::STATUS_TREATMENT_STARTED;
                $updateData['treatment_started_at'] = now();
                $updateData['investigation_pending'] = false;
            } elseif ($hasTestResults) {
                $newStatus = Prescription::STATUS_REPORT_RECEIVED;
                $updateData['investigation_pending'] = false;
                $updateData['report_received_at'] = $prescription->report_received_at ?? now();
            } else {
                $newStatus = Prescription::STATUS_INVESTIGATION_PENDING;
                $updateData['investigation_pending'] = true;
                $updateData['treatment_started_at'] = null;
            }
        }

        if ($newStatus !== $prescription->status) {
            $updateData['status'] = $newStatus;
            if ($newStatus === Prescription::STATUS_TREATMENT_STARTED) {
                $updateData['treatment_started_at'] = $updateData['treatment_started_at'] ?? now();
                $updateData['investigation_pending'] = false;
            }
            if ($newStatus === Prescription::STATUS_FOLLOW_UP) {
                $updateData['investigation_pending'] = false;
            }
            if ($newStatus === Prescription::STATUS_COMPLETED) {
                $updateData['completed_at'] = now();
            }
            if ($newStatus === Prescription::STATUS_REPORT_RECEIVED && !isset($updateData['report_received_at'])) {
                $updateData['report_received_at'] = $prescription->report_received_at ?? now();
            }
        }

        $prescription->update($updateData);

        // Log status change
        if (isset($updateData['status']) && $newStatus !== $prescription->getOriginal('status')) {
            $prescription->statusLogs()->create([
                'old_status' => $prescription->getOriginal('status'),
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        $prescription->items()->delete();

        if (!empty($data['items'])) {
        $sortOrder = 0;
        foreach ($data['items'] as $item) {
            if (empty($item['medicine_name'])) {
                continue;
            }

            $suggestionId = null;

            if (empty($item['medicine_id']) && !empty($item['medicine_name'])) {
                $normalizedName = mb_strtolower(trim($item['medicine_name']));
                $existingSuggestion = MedicineSuggestion::whereRaw('LOWER(name) = ?', [$normalizedName])
                    ->where('doctor_id', auth()->id())
                    ->where('status', '!=', MedicineSuggestion::STATUS_REJECTED)
                    ->first();

                if (!$existingSuggestion) {
                    $suggestion = MedicineSuggestion::create([
                        'name' => trim($item['medicine_name']),
                        'strength' => $item['strength'] ?? null,
                        'doctor_id' => auth()->id(),
                        'status' => MedicineSuggestion::STATUS_PENDING,
                        'reason' => 'Auto-suggested from prescription (missing medicine)',
                    ]);
                    $suggestionId = $suggestion->id;
                } else {
                    $suggestionId = $existingSuggestion->id;
                }
            }

            $prescription->items()->create([
                'medicine_id' => $item['medicine_id'] ?? null,
                'medicine_name' => $item['medicine_name'],
                'dosage' => $item['strength'] ?? null,
                'frequency' => $item['frequency'] ?? null,
                'duration' => $item['duration'] ?? null,
                'instructions' => $item['instructions'] ?? null,
                'seal_id' => $item['seal_id'] ?? null,
                'seal_text' => $item['seal_text'] ?? null,
                'seal_details' => $item['seal_details'] ?? null,
                'medicine_suggestion_id' => $suggestionId,
                'type' => 'medicine',
                'sort_order' => $sortOrder++,
            ]);
        }
        }

        if (!empty($data['seals_json'])) {
            $seals = json_decode($data['seals_json'], true);
            if (is_array($seals)) {
                foreach ($seals as $seal) {
                    $sealId = $seal['seal_id'] ?? null;
                    $sealText = $seal['seal_text'] ?? ($seal['name'] ?? '');
                    $sealDetails = $seal['seal_details'] ?? null;
                    $position = $seal['position'] ?? null;
                    $duration = $seal['duration'] ?? null;

                    if (empty($sealText)) continue;

                    if ($sealId) {
                        $sealModel = ClinicalSeal::find($sealId);
                        if ($sealModel) {
                            $sealModel->increment('used_count');
                        }
                    } elseif (!empty($sealText)) {
                        $sealModel = ClinicalSeal::findByNameOrCreate($sealText, auth()->id());
                        $sealModel->increment('used_count');
                        $sealId = $sealModel->id;
                    }

                    $prescription->items()->create([
                        'type' => 'seal',
                        'seal_id' => $sealId,
                        'seal_text' => $sealText,
                        'seal_details' => $sealDetails,
                        'medicine_name' => $sealText,
                        'duration' => $duration,
                        'sort_order' => $position ?? $sortOrder++,
                    ]);
                }
            }
        }

        $prescription->advice()->delete();
        $prescription->advices()->detach();

        if (!empty($data['advice_json'])) {
            $advices = json_decode($data['advice_json'], true);
            if (is_array($advices)) {
                $adviceIds = [];
                foreach ($advices as $advice) {
                    if (!empty($advice['id'])) {
                        $a = Advice::find($advice['id']);
                        if ($a) {
                            $a->increment('used_count');
                            $adviceIds[] = $a->id;
                        }
                    } elseif (!empty($advice['name'])) {
                        $a = Advice::findByNameOrCreate($advice['name'], auth()->id());
                        $a->increment('used_count');
                        $adviceIds[] = $a->id;
                    }
                }
                if (!empty($adviceIds)) {
                    $prescription->advices()->sync($adviceIds);
                }
            }
        } elseif (!empty($data['advice'])) {
            foreach (array_filter($data['advice']) as $i => $adviceText) {
                $a = Advice::findByNameOrCreate($adviceText, auth()->id());
                $a->increment('used_count');
                $prescription->advices()->attach($a->id);
            }
        }

        if (!empty($data['complaints_json'])) {
            $complaints = json_decode($data['complaints_json'], true);
            if (is_array($complaints)) {
                $syncData = [];
                foreach ($complaints as $i => $complaint) {
                    if (!empty($complaint['id'])) {
                        $c = Complaint::find($complaint['id']);
                        if ($c) {
                            $c->increment('used_count');
                            $syncData[$c->id] = [
                                'notes' => $complaint['notes'] ?? null,
                                'sort_order' => $i,
                            ];
                        }
                    } elseif (!empty($complaint['name'])) {
                        $c = Complaint::findByNameOrCreate($complaint['name'], auth()->id());
                        $c->increment('used_count');
                        $syncData[$c->id] = [
                            'notes' => $complaint['notes'] ?? null,
                            'sort_order' => $i,
                        ];
                    }
                }
                $prescription->complaints()->sync($syncData);
            }
        } else {
            $prescription->complaints()->sync([]);
        }

        $prescription->tests()->delete();
        $prescription->testReportResults()->delete();

        if (!empty($data['tests_json'])) {
            $tests = json_decode($data['tests_json'], true);
            if (is_array($tests)) {
                foreach ($tests as $i => $test) {
                    if (!empty($test['id'])) {
                        $lt = LaboratoryTest::find($test['id']);
                        if ($lt) {
                            $lt->increment('used_count');
                            $prescription->tests()->create([
                                'laboratory_test_id' => $lt->id,
                                'test_name' => $lt->test_name,
                                'sort_order' => $i,
                            ]);
                        }
                    } elseif (!empty($test['name'])) {
                        $lt = LaboratoryTest::findByNameOrCreate($test['name'], auth()->id());
                        $lt->increment('used_count');
                        $prescription->tests()->create([
                            'laboratory_test_id' => $lt->id,
                            'test_name' => $lt->test_name,
                            'sort_order' => $i,
                        ]);
                    }
                }
            }
        }

        if (!empty($data['medical_histories_json'])) {
            $histories = json_decode($data['medical_histories_json'], true);
            if (is_array($histories) && $prescription->patient_id) {
                foreach ($histories as $history) {
                    if (!empty($history['id'])) {
                        $mhc = MedicalHistoryCondition::find($history['id']);
                        if ($mhc) {
                            $mhc->increment('used_count');
                            $prescription->patient->medicalHistories()->updateOrCreate(
                                ['medical_history_condition_id' => $mhc->id],
                                [
                                    'condition_name' => $mhc->name,
                                    'status' => 'active',
                                ]
                            );
                        }
                    } elseif (!empty($history['name'])) {
                        $mhc = MedicalHistoryCondition::findByNameOrCreate($history['name'], auth()->id());
                        $mhc->increment('used_count');
                        $prescription->patient->medicalHistories()->updateOrCreate(
                            ['medical_history_condition_id' => $mhc->id],
                            [
                                'condition_name' => $mhc->name,
                                'status' => 'active',
                            ]
                        );
                    }
                }
            }
        }

        if (!empty($data['test_results_json'])) {
            $results = json_decode($data['test_results_json'], true);
            if (is_array($results)) {
                foreach ($results as $r) {
                    if (!empty($r['test_name'])) {
                        $prescription->testReportResults()->create([
                            'test_name' => $r['test_name'],
                            'result' => $r['result'] ?? null,
                        ]);
                    }
                }
            }
        }

        $prescription->testReports()->delete();
        if (!empty($data['test_reports_json'])) {
            $reports = json_decode($data['test_reports_json'], true);
            if (is_array($reports)) {
                $sortOrder = 0;
                foreach ($reports as $testName => $parameters) {
                    if (is_array($parameters)) {
                        foreach ($parameters as $param) {
                            if (!empty($param['parameter'])) {
                                $prescription->testReports()->create([
                                    'test_name' => $testName,
                                    'parameter_name' => $param['parameter'],
                                    'value' => $param['value'] ?? null,
                                    'unit' => $param['unit'] ?? null,
                                    'reference_range' => $param['reference_range'] ?? null,
                                    'sort_order' => $sortOrder++,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // Save feature data
        $featureData = [
            'family_history_data' => !empty($data['family_history_data']) ? json_decode($data['family_history_data'], true) : null,
            'menstrual_history_data' => !empty($data['menstrual_history_data']) ? json_decode($data['menstrual_history_data'], true) : null,
            'drug_history_data' => !empty($data['drug_history_data']) ? json_decode($data['drug_history_data'], true) : null,
            'ot_note_data' => !empty($data['ot_note_data']) ? json_decode($data['ot_note_data'], true) : null,
            'anesthesia_data' => !empty($data['anesthesia_data']) ? json_decode($data['anesthesia_data'], true) : null,
        ];
        $prescription->update($featureData);

        if ($request->has('_print') || $request->input('action') === 'print') {
            return redirect()->route('doctor.prescriptions.print', $prescription);
        }

        return redirect()->route('doctor.prescriptions.index')
            ->with('success', 'Prescription updated successfully.');
    }

    public function destroy(Prescription $prescription): RedirectResponse
    {
        if ($prescription->doctor_id !== auth()->id()) {
            abort(403);
        }

        $prescription->items()->delete();
        $prescription->delete();

        return redirect()->route('doctor.prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }

    public function print(Prescription $prescription): View
    {
        // if ($prescription->doctor_id !== auth()->id()) {
        //     abort(403);
        // }

        $prescription->load(['doctor', 'patient', 'items.medicine', 'items.seal', 'advice', 'advices', 'complaints', 'tests', 'testReports', 'testReportResults']);

        $doctorSetting = \App\Models\DoctorPrescriptionSetting::firstOrCreate(['doctor_id' => $prescription->doctor_id]);
        $customHeader = null;
        $customFooter = null;

        if ($doctorSetting) {
            if ($doctorSetting->header_enabled && $doctorSetting->header_id) {
                $customHeader = \App\Models\PrescriptionHeader::find($doctorSetting->header_id);
            }
            if ($doctorSetting->footer_enabled && $doctorSetting->footer_id) {
                $customFooter = \App\Models\PrescriptionFooter::find($doctorSetting->footer_id);
            }
        }

        return view('doctor.prescriptions.print', compact('prescription', 'customHeader', 'customFooter', 'doctorSetting'));
    }

    public function updateStatus(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->doctor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:' . implode(',', Prescription::STATUSES),
            'notes' => 'nullable|string|max:500',
        ]);

        $newStatus = $request->input('status');
        $notes = $request->input('notes');
        $oldStatus = $prescription->status;

        $prescription->status = $newStatus;

        if ($newStatus === Prescription::STATUS_REPORT_RECEIVED && !$prescription->report_received_at) {
            $prescription->report_received_at = now();
        }
        if ($newStatus === Prescription::STATUS_TREATMENT_STARTED && !$prescription->treatment_started_at) {
            $prescription->treatment_started_at = now();
            $prescription->investigation_pending = false;
        }
        if ($newStatus === Prescription::STATUS_FOLLOW_UP) {
            $prescription->investigation_pending = false;
        }
        if ($newStatus === Prescription::STATUS_COMPLETED) {
            $prescription->completed_at = now();
        }

        if ($oldStatus === Prescription::STATUS_COMPLETED && $newStatus !== Prescription::STATUS_COMPLETED) {
            $prescription->completed_at = null;
        }

        $prescription->save();

        $prescription->statusLogs()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Status changed from " . ($oldStatus ? str_replace('_', ' ', $oldStatus) : 'N/A') . " to " . str_replace('_', ' ', $newStatus) . ".");
    }
}
