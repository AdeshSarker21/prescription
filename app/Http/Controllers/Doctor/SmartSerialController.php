<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\PatientQueue;
use App\Models\SerialSession;
use App\Models\SmartSerialChamber;
use App\Models\SmartSerialSetting;
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

    public function dashboard()
    {
        $doctorId = Auth::id();
        $today = now()->toDateString();
        $doctor = Auth::user();
        $permissions = $doctor->getModulePermissions('smart_serial');
        $chambers = SmartSerialChamber::where('doctor_id', $doctorId)->where('is_active', true)->orderBy('name')->get();
        $activeChamberId = request('chamber_id');

        $sessionQuery = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today);
        if ($activeChamberId) {
            $sessionQuery->where('chamber_id', $activeChamberId);
        }
        $session = $sessionQuery->first();
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

        $currentChamber = $session?->chamber ?? $chambers->first();
        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        $voiceEnabled = $settings ? $settings->voice_enabled : true;

        return view('doctor.smart-serial.dashboard', compact(
            'session', 'stats', 'doctor', 'currentChamber', 'nextPatient',
            'currentCalled', 'emergencyCount', 'avgWaitMinutes', 'nextSerial',
            'queue', 'permissions', 'chambers', 'activeChamberId', 'voiceEnabled'
        ));
    }

    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 1) {
            return response()->json([]);
        }
        $doctorId = Auth::id();
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
        $doctorId = Auth::id();
        $today = now()->toDateString();
        $doctor = Auth::user();
        $chambers = SmartSerialChamber::where('doctor_id', $doctorId)->where('is_active', true)->orderBy('name')->get();
        $activeChamberId = request('chamber_id');
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

        return view('doctor.smart-serial.add-serial', compact('session', 'chambers', 'activeChamberId', 'nextSerial', 'doctor', 'formattedPreview'));
    }

    public function index()
    {
        $doctorId = Auth::id();
        $today = now()->toDateString();
        $doctor = Auth::user();
        $chambers = SmartSerialChamber::where('doctor_id', $doctorId)->where('is_active', true)->orderBy('name')->get();
        $activeChamberId = request('chamber_id');

        $sessionQuery = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today);
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
        $permissions = $doctor->getModulePermissions('smart_serial');
        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        return view('doctor.smart-serial.index', compact('session', 'queue', 'stats', 'permissions', 'settings', 'chambers', 'activeChamberId'));
    }

    public function startSession(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }
        $request->validate([
            'chamber_id' => 'required|exists:smart_serial_chambers,id',
        ]);
        $doctorId = Auth::id();
        $today = now()->toDateString();

        if (SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->where('chamber_id', $request->chamber_id)->exists()) {
            return back()->with('error', 'A session already exists for this chamber today.');
        }

        $chamber = SmartSerialChamber::findOrFail($request->chamber_id);
        if ($chamber->doctor_id !== $doctorId) {
            return $this->denyAccess($request, 'This chamber does not belong to you.');
        }

        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        $startingSerial = $settings ? $settings->getEffectiveStartingSerial($chamber) : 1;

        SerialSession::create([
            'doctor_id' => $doctorId,
            'chamber_id' => $chamber->id,
            'session_date' => $today,
            'session_label' => $request->input('label'),
            'status' => 'active',
            'current_serial' => $startingSerial - 1,
            'daily_serial_counter' => $startingSerial - 1,
            'total_patients' => 0,
            'started_at' => now(),
        ]);
        return back()->with('success', "Session started in {$chamber->name}.");
    }

    public function closeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $session->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', 'Session closed.');
    }

    public function pauseSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $session->update(['status' => 'paused']);
        return back()->with('success', 'Session paused.');
    }

    public function resumeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'edit_serial') || $session->doctor_id !== Auth::id()) {
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
        ]);
        $doctorId = Auth::id();
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

        // Generate formatted serial with duplicate prevention
        $formattedSerial = $session->generateNextSerial($settings ?? SmartSerialSetting::firstOrCreate(['doctor_id' => $doctorId]));

        // Ensure no duplicate (belt-and-suspenders check)
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

        // Log initial status
        $queue->logStatusChange(PatientQueue::STATUS_WAITING, 'doctor', 'Serial created');

        return back()->with('success', "Added. Serial {$formattedSerial}");
    }

    public function callNext(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        if ($session->status !== 'active') return back()->with('error', 'Session not active.');

        $nextPatient = null;
        foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
            $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
            if ($nextPatient) break;
        }
        if (!$nextPatient) return back()->with('error', 'No patients waiting.');

        // ─── CAPTURE VOICE CONTEXT BEFORE UPDATE ───
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

        $nextPatient->transitionTo(PatientQueue::STATUS_CALLING, 'doctor');
        return back()->with('success', "Called: {$name} (#{$nextPatient->formatted_serial})");
    }

    public function callPatient(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        if (!in_array($q->status, [PatientQueue::STATUS_WAITING, PatientQueue::STATUS_PREPARING])) {
            return back()->with('error', 'Patient is not waiting or preparing.');
        }

        // ─── CAPTURE VOICE CONTEXT BEFORE UPDATE ───
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

        $q->transitionTo(PatientQueue::STATUS_CALLING, 'doctor');
        return back()->with('success', "Called: {$name}");
    }

    public function startConsultation(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        // ─── CAPTURE VOICE CONTEXT BEFORE UPDATE ───
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

        $q->transitionTo(PatientQueue::STATUS_INSIDE, 'doctor');
        return back()->with('success', 'Patient entered. Consultation started.');
    }

    public function complete(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'complete')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        // ─── CAPTURE EVERYTHING BEFORE QUEUE UPDATE ───
        $patientName = $q->patient->name ?? 'রোগী';
        $patientGender = $q->patient->gender ?? 'male';
        $patientId = $q->patient_id;
        $queueId = $q->id;
        $serialId = $q->serial_session_id;
        $formattedSerial = $q->formatted_serial;
        $session = $q->session;
        $prefix = $patientGender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$patientName}, ধন্যবাদ।";

        // ─── STORE VOICE CONTEXT IN SESSION (before queue status changes) ───
        $session->update([
            'pending_announcement' => 'completed',
            'pending_queue_id' => $queueId,
            'pending_voice_text' => $voiceText,
            'pending_patient_name' => $patientName,
            'pending_patient_gender' => $patientGender,
            'pending_patient_id' => $patientId,
        ]);

        // ─── NOW UPDATE QUEUE STATUS ───
        $q->transitionTo(PatientQueue::STATUS_COMPLETED, 'doctor');

        // Log announcement in history
        \App\Models\SmartSerialAnnouncementHistory::create([
            'serial_session_id' => $serialId,
            'patient_queue_id' => $queueId,
            'patient_id' => $patientId,
            'announcement_type' => 'completed',
            'text_spoken' => $voiceText,
            'tts_provider_used' => 'dashboard_voice',
            'success' => true,
            'announced_at' => now(),
        ]);

        $settings = SmartSerialSetting::where('doctor_id', Auth::id())->first();
        if ($settings && $settings->auto_call_next) {
            if ($session->status === 'active') {
                $nextPatient = null;
                foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
                    $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
                    if ($nextPatient) break;
                }
                if ($nextPatient) {
                    // ─── CAPTURE NEXT PATIENT CONTEXT BEFORE UPDATE ───
                    $nextName = $nextPatient->patient->name ?? 'রোগী';
                    $nextGender = $nextPatient->patient->gender ?? 'male';
                    $nextPrefix = $nextGender === 'female' ? 'জনাবা' : 'জনাব';
                    $nextVoiceText = "{$nextPrefix} {$nextName}, এবার আপনি ভিতরে প্রবেশ করুন।";

                    // ─── STORE NEXT PATIENT VOICE CONTEXT ───
                    $session->update([
                        'pending_announcement' => 'calling',
                        'pending_queue_id' => $nextPatient->id,
                        'pending_voice_text' => $nextVoiceText,
                        'pending_patient_name' => $nextName,
                        'pending_patient_gender' => $nextGender,
                        'pending_patient_id' => $nextPatient->patient_id,
                    ]);

                    // ─── NOW UPDATE NEXT PATIENT QUEUE STATUS ───
                    $nextPatient->transitionTo(PatientQueue::STATUS_CALLING, 'system', 'Auto-called after completion');

                    \App\Models\SmartSerialAnnouncementHistory::create([
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
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        $q->transitionTo(PatientQueue::STATUS_CANCELLED, 'doctor');
        return back()->with('success', 'Cancelled.');
    }

    public function skip(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'skip')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        $q->transitionTo(PatientQueue::STATUS_SKIPPED, 'doctor');

        // Auto-call next patient
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
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        $q->update(['priority' => 'emergency']);

        if ($q->status === PatientQueue::STATUS_WAITING) {
            // Move currently called patient back to waiting
            PatientQueue::where('serial_session_id', $q->serial_session_id)
                ->where('status', 'calling')
                ->each(function ($patient) {
                    $patient->update(['status' => PatientQueue::STATUS_WAITING, 'called_at' => null]);
                    $patient->logStatusChange(PatientQueue::STATUS_WAITING, 'system', 'Moved back for emergency');
                });

            $q->transitionTo(PatientQueue::STATUS_CALLING, 'doctor', 'Emergency priority set');
        }

        return back()->with('success', 'Emergency set. Called immediately.');
    }

    public function prepare(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'prepare') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        if ($session->status !== 'active') return back()->with('error', 'Session not active.');

        $nextPatient = null;
        foreach (['emergency', 'urgent', 'vip', 'normal'] as $p) {
            $nextPatient = $session->patientQueues()->where('status', 'waiting')->where('priority', $p)->orderBy('serial_number')->first();
            if ($nextPatient) break;
        }
        if (!$nextPatient) return back()->with('error', 'No patients waiting.');

        $nextPatient->transitionTo(PatientQueue::STATUS_PREPARING, 'doctor');
        return back()->with('success', "Preparing: {$nextPatient->patient->name} (#{$nextPatient->formatted_serial})");
    }

    public function preparePatient(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'prepare')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        if ($q->status !== PatientQueue::STATUS_WAITING) return back()->with('error', 'Patient is not waiting.');

        $q->transitionTo(PatientQueue::STATUS_PREPARING, 'doctor');
        return back()->with('success', "Preparing: {$q->patient->name}");
    }

    public function recall(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'recall')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        // ─── CAPTURE EVERYTHING BEFORE QUEUE UPDATE ───
        $patientName = $q->patient->name ?? 'রোগী';
        $patientGender = $q->patient->gender ?? 'male';
        $patientId = $q->patient_id;
        $queueRecordId = $q->id;
        $serialId = $q->serial_session_id;
        $session = $q->session;
        $prefix = $patientGender === 'female' ? 'জনাবা' : 'জনাব';
        $voiceText = "{$prefix} {$patientName}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।";

        if ($q->status === PatientQueue::STATUS_COMPLETED) {
            // ─── STORE VOICE CONTEXT BEFORE QUEUE UPDATE ───
            $session->update([
                'pending_announcement' => 'recall',
                'pending_queue_id' => $queueRecordId,
                'pending_voice_text' => $voiceText,
                'pending_patient_name' => $patientName,
                'pending_patient_gender' => $patientGender,
                'pending_patient_id' => $patientId,
            ]);

            // ─── NOW UPDATE QUEUE STATUS ───
            $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'Recalled']);
            $q->transitionTo(PatientQueue::STATUS_CALLING, 'doctor', 'Recalled after completion');

            \App\Models\SmartSerialAnnouncementHistory::create([
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
            // ─── STORE VOICE CONTEXT BEFORE QUEUE UPDATE ───
            $session->update([
                'pending_announcement' => 'recall',
                'pending_queue_id' => $queueRecordId,
                'pending_voice_text' => $voiceText,
                'pending_patient_name' => $patientName,
                'pending_patient_gender' => $patientGender,
                'pending_patient_id' => $patientId,
            ]);

            // ─── NOW UPDATE QUEUE STATUS ───
            $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'Recalled']);
            $q->logStatusChange(PatientQueue::STATUS_CALLING, 'doctor', 'Recalled (re-announced)');

            \App\Models\SmartSerialAnnouncementHistory::create([
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
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }

        $q->update(['notes' => ($q->notes ? $q->notes . ' | ' : '') . 'No Show']);
        $q->transitionTo(PatientQueue::STATUS_CANCELLED, 'doctor', 'Marked as No Show');

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
        if ($session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this session.');
        }
        $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
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
        ]);
    }

    public function settings()
    {
        $settings = SmartSerialSetting::firstOrCreate(['doctor_id' => Auth::id()]);
        $chambers = SmartSerialChamber::where('doctor_id', Auth::id())->orderBy('name')->get();
        return view('doctor.smart-serial.settings', compact('settings', 'chambers'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'starting_serial_number' => 'required|integer|min:1',
            'max_queue_size' => 'required|integer|min:1|max:500',
            'max_serial' => 'required|integer|min:1',
            'prefix' => 'nullable|string|max:20',
            'queue_mode' => 'required|in:serial,token,appointment',
        ]);

        $settings = SmartSerialSetting::firstOrCreate(['doctor_id' => Auth::id()]);
        $settings->update($request->only([
            'auto_call_next', 'show_in_appointment', 'allow_priority',
            'max_queue_size', 'serial_reset_daily', 'notification_enabled',
            'starting_serial_number', 'auto_increment', 'prefix',
            'max_serial', 'emergency_priority', 'queue_mode',
            'voice_enabled', 'display_enabled',
        ]));
        return back()->with('success', 'Settings updated.');
    }

    public function statusHistory(Request $request, $queueId)
    {
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $logs = $q->statusLogs()->orderBy('created_at', 'desc')->get();
        return response()->json(['logs' => $logs]);
    }

    public function display(Request $request, $sessionId)
    {
        $session = SerialSession::with('doctor', 'chamber')->find($sessionId);
        if (!$session || $session->status === 'closed') {
            abort(404);
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

    public function displayStatus(Request $request, $sessionId)
    {
        $session = SerialSession::with('doctor', 'chamber')->find($sessionId);
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
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

        // ─── READ VOICE CONTEXT DIRECTLY FROM SESSION ───
        // No queue lookup needed — controller stored everything before queue update
        $pendingAnnouncement = $session->pending_announcement;
        $pendingQueueId = $session->pending_queue_id;
        $pendingPatientName = $session->pending_patient_name;
        $pendingPatientGender = $session->pending_patient_gender;
        $pendingVoiceText = $session->pending_voice_text;
        $pendingPatientId = $session->pending_patient_id;

        // Clear after reading (one-shot)
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
            'session_status' => $session->status,
            'queue' => $queue,
            'current_called' => $currentCalled,
            'next_in_queue' => $nextInQueue,
            'pending_announcement' => $pendingAnnouncement,
            'pending_queue_id' => $pendingQueueId,
            'pending_patient_name' => $pendingPatientName,
            'pending_patient_gender' => $pendingPatientGender,
            'pending_voice_text' => $pendingVoiceText,
            'pending_patient_id' => $pendingPatientId,
            'notices' => \App\Models\Notice::forDoctor($session->doctor_id)
                ->active()
                ->latest()
                ->get()
                ->map(fn($n) => ['id' => $n->id, 'title' => $n->title, 'message' => $n->message]),
            'doctor' => [
                'name' => $doctor->name ?? 'Doctor',
                'name_bn' => $doctor->name_bn ?? '',
                'avatar' => $doctor->avatar_url ?? '',
                'specialization' => $doctor->specialization ?? '',
                'specialization_bn' => $doctor->specialization_bn ?? '',
                'qualification' => $doctor->qualification ?? '',
                'qualification_bn' => $doctor->qualification_bn ?? '',
                'designation_title' => $doctor->designation_title ?? '',
                'designation_title_bn' => $doctor->designation_title_bn ?? '',
                'sub_specialties' => $doctor->sub_specialties ?? [],
                'sub_specialties_bn' => $doctor->sub_specialties_bn ?? [],
                'clinic_name' => $doctor->clinic_name ?? '',
                'clinic_name_bn' => $doctor->clinic_name_bn ?? '',
            ],
            'chamber_name' => $session->chamber->name ?? '',
        ]);
    }

    public function printToken($queueId)
    {
        $queue = PatientQueue::with(['patient', 'session.chamber', 'doctor'])->findOrFail($queueId);
        $doctor = $queue->doctor;
        $chamberName = $queue->session->chamber->name ?? '';
        $clinicName = $doctor->clinic_name ?? '';
        $clinicNameBn = $doctor->clinic_name_bn ?? '';

        return view('doctor.smart-serial.token-print', compact('queue', 'doctor', 'chamberName', 'clinicName', 'clinicNameBn'));
    }

    public function displayByDoctor($doctorId)
    {
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
}
