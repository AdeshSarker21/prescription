<?php

namespace App\Http\Controllers\Doctor;

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

    public function dashboard()
    {
        $doctorId = Auth::id();
        $today = now()->toDateString();
        $session = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->first();
        $doctor = Auth::user();
        $stats = ['total'=>0,'waiting'=>0,'called'=>0,'in_consultation'=>0,'completed'=>0,'cancelled'=>0,'no_show'=>0];
        $nextPatient = null;
        $currentCalled = null;
        $emergencyCount = 0;

        if ($session) {
            $queue = $session->patientQueues()->with('patient')->get();
            $stats = [
                'total'            => $queue->count(),
                'waiting'          => $queue->where('status','waiting')->count(),
                'called'           => $queue->where('status','called')->count(),
                'in_consultation'  => $queue->where('status','in_consultation')->count(),
                'completed'        => $queue->where('status','completed')->count(),
                'cancelled'        => $queue->where('status','cancelled')->count(),
                'no_show'          => $queue->where('status','no_show')->count(),
            ];
            $emergencyCount = $queue->where('priority','emergency')->count();
            $currentCalled = $queue->where('status','called')->first();
            foreach (['emergency','urgent','vip','normal'] as $p) {
                $nextPatient = $queue->where('status','waiting')->where('priority',$p)->sortBy('serial_number')->first();
                if ($nextPatient) break;
            }
        }

        $chambers = $doctor->chambers;
        $currentChamber = is_array($chambers) && count($chambers) > 0 ? $chambers[0] : null;

        return view('doctor.smart-serial.dashboard', compact('session', 'stats', 'doctor', 'currentChamber', 'nextPatient', 'currentCalled', 'emergencyCount'));
    }

    public function index()
    {
        $doctorId = Auth::id();
        $today = now()->toDateString();
        $session = SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->first();
        $queue = collect();
        $stats = ['total'=>0,'waiting'=>0,'called'=>0,'in_consultation'=>0,'completed'=>0,'cancelled'=>0,'no_show'=>0];
        if ($session) {
            $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
            $stats = [
                'total' => $queue->count(), 'waiting' => $queue->where('status','waiting')->count(),
                'called' => $queue->where('status','called')->count(), 'in_consultation' => $queue->where('status','in_consultation')->count(),
                'completed' => $queue->where('status','completed')->count(), 'cancelled' => $queue->where('status','cancelled')->count(),
                'no_show' => $queue->where('status','no_show')->count(),
            ];
        }
        $permissions = Auth::user()->getModulePermissions('smart_serial');
        $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        return view('doctor.smart-serial.index', compact('session', 'queue', 'stats', 'permissions', 'settings'));
    }

    public function startSession(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) {
            return $this->denyAccess($request);
        }
        $doctorId = Auth::id();
        $today = now()->toDateString();
        if (SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->exists()) {
            return back()->with('error', 'A session already exists for today.');
        }
        SerialSession::create([
            'doctor_id' => $doctorId, 'session_date' => $today, 'session_label' => $request->input('label'),
            'status' => 'active', 'current_serial' => 0, 'total_patients' => 0, 'started_at' => now(),
        ]);
        return back()->with('success', 'Session started.');
    }

    public function closeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $session->update(['status'=>'closed','closed_at'=>now()]);
        return back()->with('success','Session closed.');
    }

    public function pauseSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $session->update(['status'=>'paused']);
        return back()->with('success','Session paused.');
    }

    public function resumeSession(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        $session->update(['status'=>'active']);
        return back()->with('success','Session resumed.');
    }

    public function addPatient(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','create_serial')) {
            return $this->denyAccess($request);
        }
        $request->validate(['patient_id'=>'required|exists:patients,id','priority'=>'sometimes|string|in:normal,urgent,vip','notes'=>'nullable|string|max:500','appointment_id'=>'nullable|exists:appointments,id']);
        $doctorId = Auth::id();
        $session = SerialSession::where('doctor_id',$doctorId)->where('session_date',now()->toDateString())->where('status','!=','closed')->first();
        if (!$session) return back()->with('error','No active session.');
        if ($session->patientQueues()->where('patient_id',$request->patient_id)->whereIn('status',['waiting','called','in_consultation'])->exists()) {
            return back()->with('error','Patient already in queue.');
        }
        $nextSerial = $session->current_serial + 1;
        $session->patientQueues()->create(['doctor_id'=>$doctorId,'patient_id'=>$request->patient_id,'appointment_id'=>$request->appointment_id,'serial_number'=>$nextSerial,'status'=>'waiting','priority'=>$request->input('priority','normal'),'notes'=>$request->notes]);
        $session->update(['current_serial'=>$nextSerial,'total_patients'=>$session->total_patients+1]);
        return back()->with('success',"Added. Serial #{$nextSerial}");
    }

    public function callNext(Request $request, SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next') || $session->doctor_id !== Auth::id()) {
            return $this->denyAccess($request);
        }
        if ($session->status!=='active') return back()->with('error','Session not active.');
        $nextPatient = null;
        foreach (['emergency','urgent','vip','normal'] as $p) {
            $nextPatient = $session->patientQueues()->where('status','waiting')->where('priority',$p)->orderBy('serial_number')->first();
            if ($nextPatient) break;
        }
        if (!$nextPatient) return back()->with('error','No patients waiting.');
        $nextPatient->update(['status'=>'called','called_at'=>now()]);
        return back()->with('success',"Called: {$nextPatient->patient->name} (#{$nextPatient->serial_number})");
    }

    public function callPatient(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        if ($q->status!=='waiting') return back()->with('error','Not waiting.');
        $q->update(['status'=>'called','called_at'=>now()]);
        return back()->with('success',"Called: {$q->patient->name}");
    }

    public function startConsultation(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['status'=>'in_consultation','consultation_started_at'=>now()]);
        return back()->with('success','Consultation started.');
    }

    public function complete(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','complete')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['status'=>'completed','completed_at'=>now()]);
        return back()->with('success','Completed.');
    }

    public function cancel(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','cancel_serial')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['status'=>'cancelled','cancelled_at'=>now()]);
        return back()->with('success','Cancelled.');
    }

    public function noShow(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','cancel_serial')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['status'=>'no_show']);
        return back()->with('success','Marked no-show.');
    }

    public function recall(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','recall')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        if (!in_array($q->status,['called','completed'])) return back()->with('error','Cannot recall.');
        $q->update(['status'=>'waiting','called_at'=>null,'consultation_started_at'=>null,'completed_at'=>null]);
        return back()->with('success','Recalled.');
    }

    public function skip(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','skip')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['status'=>'waiting','called_at'=>null]);
        $next = PatientQueue::where('serial_session_id',$q->serial_session_id)->where('status','waiting')->where('id','!=',$q->id)->orderBy('serial_number')->first();
        if ($next) $next->update(['status'=>'called','called_at'=>now()]);
        return back()->with('success','Skipped. Next called.');
    }

    public function emergency(Request $request, $queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','emergency')) {
            return $this->denyAccess($request);
        }
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this queue entry.');
        }
        $q->update(['priority'=>'emergency']);
        if ($q->status==='waiting') {
            PatientQueue::where('serial_session_id',$q->serial_session_id)->where('status','called')->update(['status'=>'waiting','called_at'=>null]);
            $q->update(['status'=>'called','called_at'=>now()]);
        }
        return back()->with('success','Emergency set. Called immediately.');
    }

    public function queueStatus(Request $request, SerialSession $session)
    {
        if ($session->doctor_id!==Auth::id()) {
            return $this->denyAccess($request, 'You do not own this session.');
        }
        $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
        return response()->json(['session'=>$session->fresh(),'queue'=>$queue,'stats'=>[
            'total'=>$queue->count(),'waiting'=>$queue->where('status','waiting')->count(),
            'called'=>$queue->where('status','called')->count(),'in_consultation'=>$queue->where('status','in_consultation')->count(),
            'completed'=>$queue->where('status','completed')->count(),
        ]]);
    }

    public function settings()
    {
        $settings = SmartSerialSetting::firstOrCreate(['doctor_id'=>Auth::id()]);
        return view('doctor.smart-serial.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = SmartSerialSetting::firstOrCreate(['doctor_id'=>Auth::id()]);
        $settings->update($request->only(['auto_call_next','show_in_appointment','allow_priority','max_queue_size','serial_reset_daily','notification_enabled']));
        return back()->with('success','Settings updated.');
    }
}
