<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\PatientQueue;
use App\Models\SerialSession;
use App\Models\SerialStatusLog;
use App\Models\SmartSerialChamber;
use App\Models\SmartSerialSetting;
use App\Models\SmartSerialAnnouncementHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartSerialController extends Controller
{
    protected function denyAccess(Request $request, string $message = 'You do not have permission to perform this action.'): mixed
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'permission_denied',
                'message' => $message,
            ], 403);
        }
        abort(403, $message);
    }

    protected function getAccessibleDoctorIds(): array
    {
        return Auth::user()->getAccessibleDoctorIds();
    }

    protected function validateDoctorAccess(int $doctorId): bool
    {
        return in_array($doctorId, $this->getAccessibleDoctorIds());
    }

    protected function resolveDoctorId(Request $request): ?int
    {
        $doctorId = $request->input('doctor_id') ?? session('smart_serial_doctor_id');
        if ($doctorId && $this->validateDoctorAccess((int) $doctorId)) {
            session(['smart_serial_doctor_id' => (int) $doctorId]);
            return (int) $doctorId;
        }
        $accessible = $this->getAccessibleDoctorIds();
        if (count($accessible) === 1) {
            session(['smart_serial_doctor_id' => $accessible[0]]);
            return $accessible[0];
        }
        return null;
    }

    public function dashboard(Request $request)
    {
        $assistantId = Auth::id();
        $today = now()->toDateString();
        $accessibleDoctorIds = $this->getAccessibleDoctorIds();
        $doctors = Auth::user()->assignedDoctors()->get(['users.id', 'users.name', 'users.clinic_name']);
        $selectedDoctorId = $this->resolveDoctorId($request);

        $session = null;
        $stats = [
            'total' => 0, 'waiting' => 0, 'preparing' => 0, 'calling' => 0,
            'inside' => 0, 'completed' => 0, 'skipped' => 0,
            'cancelled' => 0, 'emergency' => 0,
        ];
        $nextPatient = null;
        $currentCalled = null;
        $emergencyCount = 0;
        $avgWaitMinutes = 0;
        $nextSerial = 0;
        $queue = collect();
        $chambers = collect();
        $activeChamberId = $request->get('chamber_id');
        $voiceEnabled = false;

        if ($selectedDoctorId) {
            $chambers = SmartSerialChamber::where('doctor_id', $selectedDoctorId)->where('is_active', true)->orderBy('name')->get();

            $sessionQuery = SerialSession::where('doctor_id', $selectedDoctorId)->where('session_date', $today);
            if ($activeChamberId) {
                $sessionQuery->where('chamber_id', $activeChamberId);
            }
            $session = $sessionQuery->first();

            if ($session) {
                $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
                $stats = [
                    'total'       => $queue->count(),
                    'waiting'     => $queue->where('status', 'waiting')->count(),
                    'preparing'   => $queue->where('status', 'preparing')->count(),
                    'calling'     => $queue->where('status', 'calling')->count(),
                    'inside'      => $queue->where('status', 'inside')->count(),
                    'completed'   => $queue->where('status', 'completed')->count(),
                    'skipped'     => $queue->where('status', 'skipped')->count(),
                    'cancelled'   => $queue->where('status', 'cancelled')->count(),
                    'emergency'   => $queue->where('status', 'emergency')->count(),
                ];
                $emergencyCount = $queue->where('priority', 'emergency')->count();
                $currentCalled = $queue->where('status', 'calling')->first();
                $nextSerial = $session->daily_serial_counter + 1;

                foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
                    $nextPatient = $queue->where('status', 'waiting')->where('priority', $p)->sortBy('serial_number')->first();
                    if ($nextPatient) break;
                }

                $completedPatients = $queue->where('status', 'completed')->filter(function ($item) {
                    return $item->called_at && $item->completed_at;
                });
                if ($completedPatients->count() > 0) {
                    $totalWait = $completedPatients->sum(function ($item) {
                        return $item->called_at->diffInSeconds($item->completed_at);
                    });
                    $avgWaitMinutes = round($totalWait / $completedPatients->count() / 60, 1);
                }
            }

            $settings = SmartSerialSetting::where('doctor_id', $selectedDoctorId)->first();
            $voiceEnabled = $settings ? $settings->voice_enabled : true;
        }

        $permissions = Auth::user()->getModulePermissions('smart_serial');

        return view('assistant.smart-serial.dashboard', compact(
            'session', 'stats', 'currentCalled', 'nextPatient',
            'emergencyCount', 'avgWaitMinutes', 'nextSerial',
            'queue', 'permissions', 'chambers', 'activeChamberId',
            'voiceEnabled', 'selectedDoctorId', 'doctors'
        ));
    }

    public function index(Request $request)
    {
        $assistantId = Auth::id();
        $today = now()->toDateString();
        $accessibleDoctorIds = $this->getAccessibleDoctorIds();
        $doctors = Auth::user()->assignedDoctors()->get(['users.id', 'users.name', 'users.clinic_name']);
        $selectedDoctorId = $this->resolveDoctorId($request);

        $session = null;
        $queue = collect();
        $stats = [
            'total' => 0, 'waiting' => 0, 'preparing' => 0, 'calling' => 0,
            'inside' => 0, 'completed' => 0, 'skipped' => 0,
            'cancelled' => 0, 'emergency' => 0,
        ];

        if ($selectedDoctorId) {
            $session = SerialSession::where('doctor_id', $selectedDoctorId)
                ->where('session_date', $today)->first();

            if ($session) {
                $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
                $stats = [
                    'total'     => $queue->count(),
                    'waiting'   => $queue->where('status', 'waiting')->count(),
                    'preparing' => $queue->where('status', 'preparing')->count(),
                    'calling'   => $queue->where('status', 'calling')->count(),
                    'inside'    => $queue->where('status', 'inside')->count(),
                    'completed' => $queue->where('status', 'completed')->count(),
                    'skipped'   => $queue->where('status', 'skipped')->count(),
                    'cancelled' => $queue->where('status', 'cancelled')->count(),
                    'emergency' => $queue->where('status', 'emergency')->count(),
                ];
            }
        }

        $permissions = Auth::user()->getModulePermissions('smart_serial');
        $settings = $selectedDoctorId ? SmartSerialSetting::where('doctor_id', $selectedDoctorId)->first() : null;

        return view('assistant.smart-serial.index', compact(
            'session', 'queue', 'stats', 'permissions', 'settings',
            'selectedDoctorId', 'doctors'
        ));
    }

    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 1) {
            return response()->json([]);
        }
        $doctorId = $request->get('doctor_id');
        if (!$doctorId || !$this->validateDoctorAccess((int) $doctorId)) {
            return response()->json([]);
        }
        $patients = \App\Models\Patient::where('doctor_id', $doctorId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('patient_number', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'phone', 'patient_number', 'gender', 'date_of_birth']);
        return response()->json($patients);
    }

    public function addSerial(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }

        $doctorId = $request->input('doctor_id') ?? session('smart_serial_doctor_id');
        if (!$doctorId || !$this->validateDoctorAccess((int) $doctorId)) {
            return back()->with('error', 'Invalid doctor selected.');
        }
        $doctorId = (int) $doctorId;
        session(['smart_serial_doctor_id' => $doctorId]);

        $today = now()->toDateString();
        $doctor = \App\Models\User::findOrFail($doctorId);
        $chambers = SmartSerialChamber::where('doctor_id', $doctorId)->where('is_active', true)->orderBy('name')->get();
        $activeChamberId = $request->get('chamber_id');
        $sessionQuery = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today);
        if ($activeChamberId) {
            $sessionQuery->where('chamber_id', $activeChamberId);
        }
        $session = $sessionQuery->first();
        $nextSerial = $session ? $session->daily_serial_counter + 1 : 1;

        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        $prefix = $settings && $session ? $settings->getEffectivePrefix($session->chamber) : '';
        $padding = $settings ? max(3, strlen((string) $settings->max_serial)) : 3;
        $formattedPreview = str_pad($nextSerial, $padding, '0', STR_PAD_LEFT);
        if ($prefix) {
            $formattedPreview = "{$prefix}-{$formattedPreview}";
        }

        $doctors = Auth::user()->assignedDoctors()->get(['users.id', 'users.name', 'users.clinic_name']);

        return view('assistant.smart-serial.add-serial', compact(
            'session', 'chambers', 'activeChamberId', 'nextSerial', 'doctor', 'formattedPreview', 'doctorId', 'doctors'
        ));
    }

    public function startSession(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }

        $doctorId = $request->input('doctor_id') ?? session('smart_serial_doctor_id');
        if (!$doctorId || !$this->validateDoctorAccess((int) $doctorId)) {
            return back()->with('error', 'Invalid doctor selected.');
        }
        $doctorId = (int) $doctorId;
        session(['smart_serial_doctor_id' => $doctorId]);

        $today = now()->toDateString();
        $chamberId = $request->input('chamber_id');

        $sessionQuery = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today);
        if ($chamberId) {
            $sessionQuery->where('chamber_id', $chamberId);
        }
        if ($sessionQuery->exists()) {
            return back()->with('error', 'Session already exists for today.');
        }

        $chamber = null;
        if ($chamberId) {
            $chamber = SmartSerialChamber::findOrFail($chamberId);
            if ($chamber->doctor_id !== $doctorId) {
                return $this->denyAccess($request, 'This chamber does not belong to the selected doctor.');
            }
        }

        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        $startingSerial = $settings ? ($chamber ? $settings->getEffectiveStartingSerial($chamber) : $settings->starting_serial_number) : 1;

        SerialSession::create([
            'doctor_id' => $doctorId,
            'chamber_id' => $chamberId,
            'session_date' => $today,
            'session_label' => $request->input('label'),
            'status' => 'active',
            'current_serial' => $startingSerial - 1,
            'daily_serial_counter' => $startingSerial - 1,
            'total_patients' => 0,
            'started_at' => now(),
        ]);

        $chamberName = $chamber ? $chamber->name : '';
        return back()->with('success', "Session started" . ($chamberName ? " in {$chamberName}" : "") . ".");
    }

    public function closeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || !$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request);
        }
        $session->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', 'Session closed.');
    }

    public function pauseSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || !$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request);
        }
        $session->update(['status' => 'paused']);
        return back()->with('success', 'Session paused.');
    }

    public function resumeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || !$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request);
        }
        $session->update(['status' => 'active']);
        return back()->with('success', 'Session resumed.');
    }

    public function addPatient(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'priority' => 'sometimes|string|in:normal,urgent,vip',
            'notes' => 'nullable|string|max:500',
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'required|exists:users,id',
        ]);

        $doctorId = (int) $request->doctor_id;
        if (!$this->validateDoctorAccess($doctorId)) {
            return $this->denyAccess($request, 'You do not have access to this doctor\'s queue.');
        }
        session(['smart_serial_doctor_id' => $doctorId]);

        $session = SerialSession::where('doctor_id', $doctorId)
            ->where('session_date', now()->toDateString())
            ->where('status', '!=', 'closed')
            ->first();
        if (!$session) return back()->with('error', 'No active session.');

        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        if ($settings) {
            $currentTotal = $session->patientQueues()->count();
            if ($currentTotal >= $settings->max_queue_size) {
                return back()->with('error', "Queue is full (max {$settings->max_queue_size}).");
            }
            if ($session->daily_serial_counter >= $settings->max_serial) {
                return back()->with('error', "Maximum serial number ({$settings->max_serial}) reached.");
            }
        }

        if ($session->patientQueues()->where('patient_id', $request->patient_id)->whereIn('status', ['waiting', 'preparing', 'calling', 'inside', 'emergency'])->exists()) {
            return back()->with('error', 'Patient already in queue.');
        }

        $formattedSerial = $session->generateNextSerial($settings ?? SmartSerialSetting::firstOrCreate(['doctor_id' => $doctorId]));

        $attempts = 0;
        while ($session->serialExists($formattedSerial) && $attempts < 10) {
            $formattedSerial = $session->generateNextSerial($settings ?? SmartSerialSetting::find($doctorId));
            $attempts++;
        }

        $nextSerial = $session->daily_serial_counter;

        $queue = $session->patientQueues()->create([
            'doctor_id' => $doctorId,
            'patient_id' => $request->patient_id,
            'appointment_id' => $request->appointment_id,
            'serial_number' => $nextSerial,
            'formatted_serial' => $formattedSerial,
            'status' => PatientQueue::STATUS_WAITING,
            'priority' => $request->input('priority', 'normal'),
            'notes' => $request->notes,
        ]);

        $session->update(['total_patients' => $session->total_patients + 1]);
        $queue->logStatusChange(PatientQueue::STATUS_WAITING, 'assistant', 'Serial created');

        return back()->with('success', "Added. Serial {$formattedSerial}");
    }

    public function callNext(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next') || !$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request);
        }
        if ($session->status !== 'active') return back()->with('error', 'Session not active.');

        $nextPatient = null;
        foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
            $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
            if ($nextPatient) break;
        }
        if (!$nextPatient) return back()->with('error', 'No patients waiting.');

        $name = $nextPatient->patient->name ?? 'রোগী';
        $gender = $nextPatient->patient->gender ?? 'male';
        $prefix = $gender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$name}, এবার আপনি ভিতরে প্রবেশ করুন।";

        $session->update([
            'pending_announcement' => 'calling',
            'pending_queue_id' => $nextPatient->id,
            'pending_voice_text' => $voiceText,
            'pending_patient_name' => $name,
            'pending_patient_gender' => $gender,
            'pending_patient_id' => $nextPatient->patient_id,
        ]);

        $nextPatient->transitionTo(PatientQueue::STATUS_CALLING, 'assistant');
        return back()->with('success', "Called: {$name} (#{$nextPatient->formatted_serial})");
    }

    public function callPatient(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }
        if (!in_array($q->status, [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_PREPARING])) {
            return back()->with('error', 'Patient is not waiting or preparing.');
        }

        $name = $q->patient->name ?? 'রোগী';
        $gender = $q->patient->gender ?? 'male';
        $prefix = $gender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$name}, এবার আপনি ভিতরে প্রবেশ করুন।";

        $q->session->update([
            'pending_announcement' => 'calling',
            'pending_queue_id' => $q->id,
            'pending_voice_text' => $voiceText,
            'pending_patient_name' => $name,
            'pending_patient_gender' => $gender,
            'pending_patient_id' => $q->patient_id,
        ]);

        $q->transitionTo(PatientQueue::STATUS_CALLING, 'assistant');
        return back()->with('success', "Called: {$name}");
    }

    public function startConsultation(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $name = $q->patient->name ?? 'রোগী';
        $gender = $q->patient->gender ?? 'male';
        $prefix = $gender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$name}, এবার আপনি ভিতরে প্রবেশ করুন।";

        $q->session->update([
            'pending_announcement' => 'inside',
            'pending_queue_id' => $q->id,
            'pending_voice_text' => $voiceText,
            'pending_patient_name' => $name,
            'pending_patient_gender' => $gender,
            'pending_patient_id' => $q->patient_id,
        ]);

        $q->transitionTo(PatientQueue::STATUS_INSIDE, 'assistant');
        return back()->with('success', 'Patient entered. Consultation started.');
    }

    public function complete(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'complete')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $patientName = $q->patient->name ?? 'রোগী';
        $patientGender = $q->patient->gender ?? 'male';
        $patientId = $q->patient_id;
        $queueRecordId = $q->id;
        $serialId = $q->serial_session_id;
        $session = $q->session;
        $prefix = $patientGender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$patientName}, ধন্যবাদ।";

        $session->update([
            'pending_announcement' => 'completed',
            'pending_queue_id' => $queueRecordId,
            'pending_voice_text' => $voiceText,
            'pending_patient_name' => $patientName,
            'pending_patient_gender' => $patientGender,
            'pending_patient_id' => $patientId,
        ]);

        $q->transitionTo(PatientQueue::STATUS_COMPLETED, 'assistant');

        SmartSerialAnnouncementHistory::create([
            'serial_session_id' => $serialId,
            'patient_queue_id' => $queueRecordId,
            'patient_id' => $patientId,
            'announcement_type' => 'completed',
            'text_spoken' => $voiceText,
            'tts_provider_used' => 'dashboard_voice',
            'success' => true,
            'announced_at' => now(),
        ]);

        $settings = SmartSerialSetting::where('doctor_id', $q->doctor_id)->first();
        if ($settings && $settings->auto_call_next) {
            if ($session->status === 'active') {
                $nextPatient = null;
                foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
                    $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
                    if ($nextPatient) break;
                }
                if ($nextPatient) {
                    $nextName = $nextPatient->patient->name ?? 'রোগী';
                    $nextGender = $nextPatient->patient->gender ?? 'male';
                    $nextPrefix = $nextGender === 'female' ? 'জনাবা' : 'জনাব';
                    $nextVoiceText = "{$nextPrefix} {$nextName}, এবার আপনি ভিতরে প্রবেশ করুন।";

                    $session->update([
                        'pending_announcement' => 'calling',
                        'pending_queue_id' => $nextPatient->id,
                        'pending_voice_text' => $nextVoiceText,
                        'pending_patient_name' => $nextName,
                        'pending_patient_gender' => $nextGender,
                        'pending_patient_id' => $nextPatient->patient_id,
                    ]);

                    $nextPatient->transitionTo(PatientQueue::STATUS_CALLING, 'system', 'Auto-called after completion');

                    SmartSerialAnnouncementHistory::create([
                        'serial_session_id' => $session->id,
                        'patient_queue_id' => $nextPatient->id,
                        'patient_id' => $nextPatient->patient_id,
                        'announcement_type' => 'calling',
                        'text_spoken' => $nextVoiceText,
                        'tts_provider_used' => 'dashboard_voice',
                        'success' => true,
                        'announced_at' => now(),
                    ]);

                    return back()->with('success', 'Completed. Auto-called: ' . $nextName);
                }
            }
        }

        return back()->with('success', 'Completed.');
    }

    public function cancel(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'cancel_serial')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $q->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant');
        return back()->with('success', 'Cancelled.');
    }

    public function skip(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'skip')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $q->transitionTo(PatientQueue::STATUS_SKIPPED, 'assistant');

        $next = PatientQueue::where('serial_session_id', $q->serial_session_id)
            ->where('status', 'waiting')
            ->where('id', '!=', $q->id)
            ->orderBy('serial_number')
            ->first();
        if ($next) {
            $next->transitionTo(PatientQueue::STATUS_CALLING, 'system', 'Auto-called after skip');
        }
        return back()->with('success', 'Skipped. Next called.');
    }

    public function emergency(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'emergency')) {
            return $this->denyAccess($request);
        }

        $settings = SmartSerialSetting::where('doctor_id', Auth::id())->first();
        if ($settings && !$settings->emergency_priority) {
            return back()->with('error', 'Emergency priority is disabled in settings.');
        }

        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $q->update(['priority' => 'emergency']);

        if ($q->status === PatientQueue::STATUS_WAITING) {
            PatientQueue::where('serial_session_id', $q->serial_session_id)
                ->where('status', 'calling')
                ->each(function ($patient) {
                    $patient->update(['status' => PatientQueue::STATUS_WAITING, 'called_at' => null]);
                    $patient->logStatusChange(PatientQueue::STATUS_WAITING, 'system', 'Moved back for emergency');
                });

            $q->transitionTo(PatientQueue::STATUS_CALLING, 'assistant', 'Emergency priority set');
        }

        return back()->with('success', 'Emergency set. Called immediately.');
    }

    public function prepare(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'prepare') || !$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request);
        }
        if ($session->status !== 'active') return back()->with('error', 'Session not active.');

        $nextPatient = null;
        foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
            $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
            if ($nextPatient) break;
        }
        if (!$nextPatient) return back()->with('error', 'No patients waiting.');

        $nextPatient->transitionTo(PatientQueue::STATUS_PREPARING, 'assistant');
        return back()->with('success', "Preparing: {$nextPatient->patient->name} (#{$nextPatient->formatted_serial})");
    }

    public function preparePatient(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'prepare')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }
        if ($q->status !== PatientQueue::STATUS_WAITING) return back()->with('error', 'Patient is not waiting.');

        $q->transitionTo(PatientQueue::STATUS_PREPARING, 'assistant');
        return back()->with('success', "Preparing: {$q->patient->name}");
    }

    public function recall(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'recall')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $patientName = $q->patient->name ?? 'রোগী';
        $patientGender = $q->patient->gender ?? 'male';
        $patientId = $q->patient_id;
        $queueRecordId = $q->id;
        $serialId = $q->serial_session_id;
        $session = $q->session;
        $prefix = $patientGender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$patientName}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।";

        if ($q->status === PatientQueue::STATUS_COMPLETED) {
            $session->update([
                'pending_announcement' => 'recall',
                'pending_queue_id' => $queueRecordId,
                'pending_voice_text' => $voiceText,
                'pending_patient_name' => $patientName,
                'pending_patient_gender' => $patientGender,
                'pending_patient_id' => $patientId,
            ]);

            $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'Recalled']);
            $q->transitionTo(PatientQueue::STATUS_CALLING, 'assistant', 'Recalled after completion');

            SmartSerialAnnouncementHistory::create([
                'serial_session_id' => $serialId,
                'patient_queue_id' => $queueRecordId,
                'patient_id' => $patientId,
                'announcement_type' => 'recall',
                'text_spoken' => $voiceText,
                'tts_provider_used' => 'dashboard_voice',
                'success' => true,
                'announced_at' => now(),
            ]);

            return back()->with('success', "Recalled: {$patientName}");
        }

        if ($q->status === PatientQueue::STATUS_CALLING) {
            $session->update([
                'pending_announcement' => 'recall',
                'pending_queue_id' => $queueRecordId,
                'pending_voice_text' => $voiceText,
                'pending_patient_name' => $patientName,
                'pending_patient_gender' => $patientGender,
                'pending_patient_id' => $patientId,
            ]);

            $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'Recalled']);
            $q->logStatusChange(PatientQueue::STATUS_CALLING, 'assistant', 'Recalled (re-announced)');

            SmartSerialAnnouncementHistory::create([
                'serial_session_id' => $serialId,
                'patient_queue_id' => $queueRecordId,
                'patient_id' => $patientId,
                'announcement_type' => 'recall',
                'text_spoken' => $voiceText,
                'tts_provider_used' => 'dashboard_voice',
                'success' => true,
                'announced_at' => now(),
            ]);

            return back()->with('success', "Recalled: {$patientName}");
        }

        return back()->with('error', 'Patient cannot be recalled from current status.');
    }

    public function noShow(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'skip')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this queue entry.');
        }

        $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'No Show']);
        $q->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant', 'Marked as No Show');

        $next = PatientQueue::where('serial_session_id', $q->serial_session_id)
            ->where('status', 'waiting')
            ->where('id', '!=', $q->id)
            ->orderBy('serial_number')
            ->first();
        if ($next) {
            $next->transitionTo(PatientQueue::STATUS_CALLING, 'system', 'Auto-called after no-show');
        }
        return back()->with('success', 'Marked as No Show. Next called.');
    }

    public function queueStatus(Request $request, SerialSession $session)
    {
        if (!$this->validateDoctorAccess($session->doctor_id)) {
            return $this->denyAccess($request, 'You do not have access to this session.');
        }
        $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();

        $pendingAnnouncement = $session->pending_announcement;
        $pendingQueueId = $session->pending_queue_id;
        $pendingPatientName = $session->pending_patient_name;
        $pendingPatientGender = $session->pending_patient_gender;
        $pendingVoiceText = $session->pending_voice_text;
        $pendingPatientId = $session->pending_patient_id;

        if ($pendingAnnouncement) {
            $session->update([
                'pending_announcement' => null,
                'pending_queue_id' => null,
                'pending_voice_text' => null,
                'pending_patient_name' => null,
                'pending_patient_gender' => null,
                'pending_patient_id' => null,
            ]);
        }

        return response()->json([
            'session' => $session->fresh(),
            'queue' => $queue,
            'stats' => [
                'total' => $queue->count(),
                'waiting' => $queue->where('status', 'waiting')->count(),
                'preparing' => $queue->where('status', 'preparing')->count(),
                'calling' => $queue->where('status', 'calling')->count(),
                'inside' => $queue->where('status', 'inside')->count(),
                'completed' => $queue->where('status', 'completed')->count(),
                'skipped' => $queue->where('status', 'skipped')->count(),
                'cancelled' => $queue->where('status', 'cancelled')->count(),
                'emergency' => $queue->where('status', 'emergency')->count(),
            ],
            'pending_announcement' => $pendingAnnouncement,
            'pending_queue_id' => $pendingQueueId,
            'pending_patient_name' => $pendingPatientName,
            'pending_patient_gender' => $pendingPatientGender,
            'pending_voice_text' => $pendingVoiceText,
            'pending_patient_id' => $pendingPatientId,
        ]);
    }

    public function printToken($queueId)
    {
        $queue = PatientQueue::with(['patient', 'session.chamber', 'doctor'])->findOrFail($queueId);
        $accessibleDoctorIds = $this->getAccessibleDoctorIds();

        if (!in_array($queue->doctor_id, $accessibleDoctorIds)) {
            abort(403, 'You do not have access to print this token.');
        }

        $doctor = $queue->doctor;
        $chamberName = $queue->session->chamber->name ?? '';
        $clinicName = $doctor->clinic_name ?? '';
        $clinicNameBn = $doctor->clinic_name_bn ?? '';

        return view('doctor.smart-serial.token-print', compact('queue', 'doctor', 'chamberName', 'clinicName', 'clinicNameBn'));
    }

    public function displayByDoctor($doctorId)
    {
        $accessibleDoctorIds = $this->getAccessibleDoctorIds();

        if (!in_array((int) $doctorId, $accessibleDoctorIds)) {
            abort(403, 'You do not have access to this doctor\'s display.');
        }

        $today = now()->toDateString();
        $session = SerialSession::with('doctor', 'chamber')
            ->where('doctor_id', $doctorId)
            ->where('session_date', $today)
            ->whereIn('status', ['active', 'paused'])
            ->latest()
            ->first();

        if (!$session) {
            abort(404, 'No active session found for this doctor today.');
        }

        $queue = $session->patientQueues()
            ->with('patient')
            ->whereIn('status', ['waiting', 'preparing', 'calling', 'inside'])
            ->orderBy('serial_number')
            ->get();

        $currentCalled = $session->patientQueues()
            ->with('patient')
            ->where('status', 'calling')
            ->first();

        $nextInQueue = $session->patientQueues()
            ->with('patient')
            ->where('status', 'preparing')
            ->orderBy('serial_number')
            ->first();

        $doctor = $session->doctor;
        $chamberName = $session->chamber->name ?? '';
        $settings = SmartSerialSetting::where('doctor_id', $doctor->id)->first();

        return view('doctor.smart-serial.display', compact(
            'session', 'queue', 'currentCalled', 'nextInQueue', 'doctor', 'chamberName', 'settings'
        ));
    }

    public function history(Request $request)
    {
        $accessibleDoctorIds = $this->getAccessibleDoctorIds();
        $doctors = Auth::user()->assignedDoctors()->get(['users.id', 'users.name', 'users.clinic_name']);
        $selectedDoctorId = $this->resolveDoctorId($request);

        $history = collect();
        $dateFrom = $request->get('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        if ($selectedDoctorId) {
            $history = SerialSession::where('doctor_id', $selectedDoctorId)
                ->whereBetween('session_date', [$dateFrom, $dateTo])
                ->with(['patientQueues' => function ($q) {
                    $q->with('patient')->orderBy('serial_number');
                }])
                ->orderByDesc('session_date')
                ->orderByDesc('started_at')
                ->paginate(20);
        }

        $permissions = Auth::user()->getModulePermissions('smart_serial');

        return view('assistant.smart-serial.history', compact(
            'history', 'selectedDoctorId', 'doctors', 'dateFrom', 'dateTo', 'permissions'
        ));
    }

    public function statusHistory(Request $request, $queueId)
    {
        $q = PatientQueue::findOrFail($queueId);
        if (!$this->validateDoctorAccess($q->doctor_id)) {
            return $this->denyAccess($request);
        }
        $logs = $q->statusLogs()->orderBy('created_at', 'desc')->get();
        return response()->json(['logs' => $logs]);
    }

    public function setDoctor(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        if (!$doctorId || !$this->validateDoctorAccess((int) $doctorId)) {
            return back()->with('error', 'Invalid doctor selected.');
        }
        session(['smart_serial_doctor_id' => (int) $doctorId]);
        return back()->with('success', 'Doctor selected.');
    }
}
