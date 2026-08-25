<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\SerialSession;
use App\Models\SmartSerialChamber;
use App\Models\SmartSerialSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartSerialController extends Controller
{
    public function index(Request $request)
    {
        $assistantId = Auth::id();
        $today = now()->toDateString();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();
        $activeChamberId = $request->chamber_id;

        $chambers = SmartSerialChamber::whereIn('doctor_id', $accessibleDoctorIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sessionQuery = SerialSession::whereIn('doctor_id', $accessibleDoctorIds)
            ->where('session_date', $today)
            ->whereIn('status', ['active', 'paused']);

        if ($activeChamberId) {
            $sessionQuery->where('chamber_id', $activeChamberId);
        }

        $session = $sessionQuery->first();

        $queue = collect();
        $stats = [
            'total' => 0, 'waiting' => 0, 'preparing' => 0, 'calling' => 0,
            'inside' => 0, 'completed' => 0, 'skipped' => 0,
            'cancelled' => 0, 'emergency' => 0,
        ];

        if ($session) {
            $queue = $session->patientQueues()
                ->with('patient')
                ->orderBy('serial_number')
                ->get();

            $stats = [
                'total'     => $queue->count(),
                'waiting'   => $queue->where('status', 'waiting')->count(),
                'preparing' => $queue->where('status', 'preparing')->count(),
                'calling'   => $queue->where('status', 'calling')->count(),
                'inside'    => $queue->where('status', 'inside')->count(),
                'completed' => $queue->where('status', 'completed')->count(),
                'skipped'   => $queue->where('status', 'skipped')->count(),
                'cancelled' => $queue->where('status', 'cancelled')->count(),
                'emergency' => $queue->where('priority', 'emergency')->count(),
            ];
        }

        $doctorName = $session?->doctor?->name ?? 'N/A';
        $chamberName = $session?->chamber?->name ?? '';

        return view('receptionist.smart-serial.index', compact(
            'session', 'queue', 'stats', 'chambers', 'activeChamberId', 'doctorName', 'chamberName'
        ));
    }

    public function addPatient(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'priority' => 'sometimes|string|in:normal,urgent,vip',
            'notes' => 'nullable|string|max:500',
        ]);

        $assistantId = Auth::id();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();

        $session = SerialSession::whereIn('doctor_id', $accessibleDoctorIds)
            ->where('session_date', now()->toDateString())
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if (!$session) {
            return back()->with('error', 'No active session found.');
        }

        if ($session->patientQueues()
            ->where('patient_id', $request->patient_id)
            ->whereIn('status', ['waiting', 'preparing', 'calling', 'inside', 'emergency'])
            ->exists()
        ) {
            return back()->with('error', 'Patient is already in the queue.');
        }

        $doctorId = $session->doctor_id;
        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        if (!$settings) {
            $settings = SmartSerialSetting::firstOrCreate(['doctor_id' => $doctorId]);
        }

        $formattedSerial = $session->generateNextSerial($settings);
        $attempts = 0;
        while ($session->serialExists($formattedSerial) && $attempts < 10) {
            $formattedSerial = $session->generateNextSerial($settings);
            $attempts++;
        }

        $nextSerial = $session->daily_serial_counter;

        $queue = $session->patientQueues()->create([
            'doctor_id' => $doctorId,
            'patient_id' => $request->patient_id,
            'serial_number' => $nextSerial,
            'formatted_serial' => $formattedSerial,
            'status' => PatientQueue::STATUS_WAITING,
            'priority' => $request->input('priority', 'normal'),
            'notes' => $request->notes,
        ]);

        $session->update(['total_patients' => $session->total_patients + 1]);
        $queue->logStatusChange(PatientQueue::STATUS_WAITING, 'receptionist', 'Serial created by receptionist');

        return back()->with('success', "Patient added. Serial: {$formattedSerial}");
    }

    public function searchPatients(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $assistantId = Auth::id();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();

        $patients = Patient::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('patient_id', 'like', "%{$query}%");
            })
            ->where(function ($q) use ($accessibleDoctorIds) {
                $q->whereNull('doctor_id')
                  ->orWhereIn('doctor_id', $accessibleDoctorIds);
            })
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'phone' => $p->phone ?? '',
                    'patient_id' => $p->patient_id ?? '',
                ];
            });

        return response()->json($patients);
    }

    public function updateEntry(Request $request, $queueId)
    {
        $queue = PatientQueue::with(['session'])->findOrFail($queueId);

        $assistantId = Auth::id();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();

        if (!in_array($queue->doctor_id, $accessibleDoctorIds)) {
            abort(403, 'You do not have access to edit this entry.');
        }

        if (in_array($queue->status, ['completed', 'cancelled', 'skipped'])) {
            return back()->with('error', 'Cannot edit a completed, cancelled, or skipped entry.');
        }

        $request->validate([
            'priority' => 'sometimes|string|in:normal,urgent,vip',
            'notes' => 'nullable|string|max:500',
        ]);

        $updates = [];
        if ($request->has('priority')) {
            $updates['priority'] = $request->priority;
        }
        if ($request->has('notes')) {
            $updates['notes'] = $request->notes;
        }

        if (!empty($updates)) {
            $queue->update($updates);
            $queue->logStatusChange($queue->status, 'receptionist', 'Entry updated by receptionist');
        }

        return back()->with('success', 'Queue entry updated.');
    }

    public function cancel(Request $request, $queueId)
    {
        $queue = PatientQueue::findOrFail($queueId);

        $assistantId = Auth::id();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();

        if (!in_array($queue->doctor_id, $accessibleDoctorIds)) {
            abort(403, 'You do not have access to cancel this entry.');
        }

        if (in_array($queue->status, ['completed', 'cancelled', 'skipped'])) {
            return back()->with('error', 'Cannot cancel this entry.');
        }

        $queue->transitionTo(PatientQueue::STATUS_CANCELLED, 'receptionist');

        return back()->with('success', 'Queue entry cancelled.');
    }

    public function printToken($queueId)
    {
        $queue = PatientQueue::with(['patient', 'session.chamber', 'doctor'])->findOrFail($queueId);

        $assistantId = Auth::id();
        $accessibleDoctorIds = Auth::user()->getAccessibleDoctorIds();

        if (!in_array($queue->doctor_id, $accessibleDoctorIds)) {
            abort(403, 'You do not have access to print this token.');
        }

        $doctor = $queue->doctor;
        $chamberName = $queue->session->chamber->name ?? '';
        $clinicName = $doctor->clinic_name ?? '';
        $clinicNameBn = $doctor->clinic_name_bn ?? '';

        return view('doctor.smart-serial.token-print', compact('queue', 'doctor', 'chamberName', 'clinicName', 'clinicNameBn'));
    }
}
