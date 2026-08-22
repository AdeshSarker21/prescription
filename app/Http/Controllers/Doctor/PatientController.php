<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientDiagnosis;
use App\Models\PatientMedicalHistory;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function quickStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female,other',
            'age' => 'nullable|integer|min:0|max:150',
            'address' => 'nullable|string',
        ]);

        if (isset($data['age']) && $data['age'] !== '' && $data['age'] !== null) {
            $data['date_of_birth'] = now()->subYears((int) $data['age'])->startOfYear()->toDateString();
        }
        unset($data['age']);

        $data['doctor_id'] = auth()->id();
        $data['patient_number'] = 'PT-' . date('Ymd') . '-' . str_pad((Patient::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        $patient = Patient::create($data);

        $this->sendWelcomeSms($patient);

        return response()->json([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'gender' => $patient->gender,
                'age' => $request->input('age'),
                'address' => $patient->address,
            ],
        ]);
    }

    public function index(Request $request): View
    {
        $search = $request->get('search');

        $patients = Patient::where('doctor_id', auth()->id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('doctor.patients.index', compact('patients'));
    }

    public function create(): View
    {
        return view('doctor.patients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email',
            'phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|string|in:male,female,other',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'occupation' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
        ]);

        if (isset($data['age']) && $data['age'] !== '' && $data['age'] !== null) {
            $data['date_of_birth'] = now()->subYears((int) $data['age'])->startOfYear()->toDateString();
        }
        unset($data['age']);

        $data['doctor_id'] = auth()->id();
        $data['patient_number'] = 'PT-' . date('Ymd') . '-' . str_pad((Patient::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        Patient::create($data);

        $patient = Patient::where('doctor_id', auth()->id())->latest()->first();
        $this->sendWelcomeSms($patient);

        return redirect()->route('doctor.patients.index')
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient): View
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $prescriptions = $patient->prescriptions()
            ->with(['items', 'tests', 'testReports', 'advice'])
            ->latest()
            ->get();

        $allergies = $patient->allergies()->latest()->get();
        $medicalHistories = $patient->medicalHistories()->latest()->get();
        $diagnoses = $patient->diagnoses()->with('prescription')->latest()->get();
        $followUps = FollowUp::where('patient_id', $patient->id)
            ->where('doctor_id', auth()->id())
            ->latest('follow_up_date')
            ->get();

        // Aggregate all test reports across all prescriptions for investigation history
        $investigationHistory = $prescriptions->flatMap(function ($p) {
            return $p->testReports->map(function ($r) use ($p) {
                $r->prescription_date = $p->created_at;
                $r->prescription_number = $p->prescription_number;
                return $r;
            });
        })->groupBy('test_name');

        return view('doctor.patients.show', compact(
            'patient',
            'prescriptions',
            'allergies',
            'medicalHistories',
            'diagnoses',
            'followUps',
            'investigationHistory'
        ));
    }

    public function edit(Patient $patient): View
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        return view('doctor.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email,' . $patient->id,
            'phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|string|in:male,female,other',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'occupation' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
        ]);

        if (isset($data['age']) && $data['age'] !== '' && $data['age'] !== null) {
            $data['date_of_birth'] = now()->subYears((int) $data['age'])->startOfYear()->toDateString();
        } elseif (array_key_exists('age', $data) && ($data['age'] === '' || $data['age'] === null)) {
            $data['date_of_birth'] = null;
        }
        unset($data['age']);

        $patient->update($data);

        return redirect()->route('doctor.patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $patient->delete();

        return redirect()->route('doctor.patients.index')
            ->with('success', 'Patient deleted successfully.');
    }

    public function history(Patient $patient): View
    {
        if ($patient->doctor_id !== auth()->id()) {
            abort(403);
        }

        $prescriptions = $patient->prescriptions()
            ->with('items')
            ->latest()
            ->get();

        return view('doctor.patients.history', compact('patient', 'prescriptions'));
    }

    // ─── EMR Sub-Resource Methods ───────────────────────────────

    public function storeAllergy(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'allergy' => 'required|string|max:255',
            'severity' => 'required|in:mild,moderate,severe',
            'reaction' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $patient->allergies()->create($data);

        return back()->with('success', 'Allergy added successfully.');
    }

    public function updateAllergy(Request $request, Patient $patient, PatientAllergy $allergy): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);
        if ($allergy->patient_id !== $patient->id) abort(404);

        $data = $request->validate([
            'allergy' => 'required|string|max:255',
            'severity' => 'required|in:mild,moderate,severe',
            'reaction' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $allergy->update($data);

        return back()->with('success', 'Allergy updated successfully.');
    }

    public function destroyAllergy(Patient $patient, PatientAllergy $allergy): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);
        if ($allergy->patient_id !== $patient->id) abort(404);

        $allergy->delete();

        return back()->with('success', 'Allergy removed.');
    }

    public function storeMedicalHistory(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'condition_name' => 'required|string|max:255',
            'diagnosed_date' => 'nullable|date',
            'status' => 'required|in:active,resolved',
            'notes' => 'nullable|string',
        ]);

        $patient->medicalHistories()->create($data);

        return back()->with('success', 'Medical history added.');
    }

    public function updateMedicalHistory(Request $request, Patient $patient, PatientMedicalHistory $history): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);
        if ($history->patient_id !== $patient->id) abort(404);

        $data = $request->validate([
            'condition_name' => 'required|string|max:255',
            'diagnosed_date' => 'nullable|date',
            'status' => 'required|in:active,resolved',
            'notes' => 'nullable|string',
        ]);

        $history->update($data);

        return back()->with('success', 'Medical history updated.');
    }

    public function destroyMedicalHistory(Patient $patient, PatientMedicalHistory $history): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);
        if ($history->patient_id !== $patient->id) abort(404);

        $history->delete();

        return back()->with('success', 'Medical history removed.');
    }

    public function storeDiagnosis(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->doctor_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'diagnosis' => 'required|string|max:255',
            'icd_code' => 'nullable|string|max:20',
            'type' => 'required|in:primary,complication,comorbidity',
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data['doctor_id'] = auth()->id();

        $patient->diagnoses()->create($data);

        return back()->with('success', 'Diagnosis added.');
    }

    protected function sendWelcomeSms(Patient $patient): void
    {
        if (empty($patient->phone)) return;

        $doctor = auth()->user();
        if (!$doctor->sms_setting || !$doctor->sms_setting->is_active || !$doctor->sms_setting->welcome_sms_enabled) return;

        $template = \App\Models\SmsTemplate::where('type', 'welcome')
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->first();

        if (!$template) return;

        $message = str_replace(
            ['{{patient_name}}', '{{doctor_name}}', '{{clinic_name}}'],
            [$patient->name, $doctor->name, $doctor->clinic_name ?? ''],
            $template->body
        );

        SmsService::send($doctor->id, $patient->phone, $message, 'welcome', $patient->id);
    }
}
