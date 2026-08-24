<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\PatientQueue;
use App\Models\SerialSession;
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

    public function index()
    {
        $assistantId = Auth::id();
        $today = now()->toDateString();
        $accessibleDoctorIds = Auth::user()->_accessible_doctor_ids ?? [$assistantId];

        $session = SerialSession::whereIn('doctor_id', $accessibleDoctorIds)
            ->where('session_date', $today)->first();

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
        $permissions = Auth::user()->getModulePermissions('smart_serial');
        $settings = $session ? SmartSerialSetting::where('doctor_id', $session->doctor_id)->first() : null;
        return view('assistant.smart-serial.index', compact('session', 'queue', 'stats', 'permissions', 'settings'));
    }

    public function startSession(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }
        $doctorId = Auth::id();
        $today = now()->toDateString();
        if (SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->exists()) {
            return back()->with('error', 'Session already exists for today.');
        }
        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        $startingSerial = $settings ? $settings->starting_serial_number : 1;
        SerialSession::create([
            'doctor_id' => $doctorId,
            'session_date' => $today,
            'session_label' => $request->input('label'),
            'status' => 'active',
            'current_serial' => $startingSerial - 1,
            'daily_serial_counter' => $startingSerial - 1,
            'total_patients' => 0,
            'started_at' => now(),
        ]);
        return back()->with('success', 'Session started.');
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

        if ($session->patientQueues()->where('patient_id', $request->patient_id)->whereIn('status', ['waiting', 'preparing', 'calling', 'inside', 'emergency'])->exists()) {
            return back()->with('error', 'Patient already in queue.');
        }

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

        $nextPatient->transitionTo(PatientQueue::STATUS_CALLING, 'assistant');
        return back()->with('success', "Called: {$nextPatient->patient->name} (#{$nextPatient->formatted_serial})");
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

        $q->transitionTo(PatientQueue::STATUS_CALLING, 'assistant');
        return back()->with('success', "Called: {$q->patient->name}");
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

        $q->transitionTo(PatientQueue::STATUS_INSIDE, 'assistant');
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

        $q->transitionTo(PatientQueue::STATUS_COMPLETED, 'assistant');
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

        $q->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant');
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
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id !== Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
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

        $nextPatient->transitionTo(PatientQueue::STATUS_PREPARING, 'assistant');
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

        $q->transitionTo(PatientQueue::STATUS_PREPARING, 'assistant');
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

        if ($q->status === PatientQueue::STATUS_COMPLETED) {
            $q->transitionTo(PatientQueue::STATUS_CALLING, 'assistant', 'Recalled after completion');
            return back()->with('success', "Recalled: {$q->patient->name}");
        }

        if ($q->status === PatientQueue::STATUS_CALLING) {
            $q->logStatusChange(PatientQueue::STATUS_CALLING, 'assistant', 'Re-announced');
            return back()->with('success', "Re-announced: {$q->patient->name}");
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
}
