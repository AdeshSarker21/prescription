<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function create()
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();

        return view('assistant.patients.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'age' => 'required|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'blood_group' => 'nullable|max:10',
            'doctor_id' => 'required|exists:users,id',
            'weight' => 'nullable|string|max:10',
            'height' => 'nullable|string|max:10',
        ]);

        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($request->doctor_id, $doctorIds)) {
            return back()->with('error', 'You are not authorized to register patients for this doctor.');
        }

        $dateOfBirth = null;
        if ($request->filled('age')) {
            $dateOfBirth = now()->subYears((int) $request->age)->startOfYear()->toDateString();
        }

        $patientId = 'PID-' . strtoupper(Str::random(8));

        Patient::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'date_of_birth' => $dateOfBirth,
            'gender' => $request->gender,
            'address' => $request->address,
            'blood_group' => $request->blood_group,
            'doctor_id' => $request->doctor_id,
            'patient_id' => $patientId,
            'weight' => $request->weight,
            'height' => $request->height,
        ]);

        if ($request->input('and_appointment')) {
            return redirect()->route('assistant.appointments.create')
                ->with('success', "Patient {$patientId} registered. Now book an appointment.");
        }

        return redirect()->route('assistant.patients.create')
            ->with('success', "Patient registered successfully. ID: {$patientId}");
    }

    public function search(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $query = $request->get('q', '');

        $patients = Patient::whereIn('doctor_id', $doctorIds)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('patient_id', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'phone', 'patient_id']);

        return response()->json($patients);
    }

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

        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $data['doctor_id'] = $doctorIds->first();
        $data['patient_number'] = 'PT-' . date('Ymd') . '-' . str_pad((Patient::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        $patient = Patient::create($data);

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
}
