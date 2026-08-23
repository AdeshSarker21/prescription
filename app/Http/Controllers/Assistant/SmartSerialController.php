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
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !$user->hasModulePermission('smart_serial', 'view')) {
                abort(403, 'You do not have permission to access Smart Serial.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $assistantId = Auth::id();
        $today = now()->toDateString();
        $accessibleDoctorIds = Auth::user()->_accessible_doctor_ids ?? [$assistantId];

        $session = SerialSession::whereIn('doctor_id', $accessibleDoctorIds)
            ->where('session_date', $today)->first();

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
        $settings = $session ? SmartSerialSetting::where('doctor_id', $session->doctor_id)->first() : null;
        return view('assistant.smart-serial.index', compact('session', 'queue', 'stats', 'permissions', 'settings'));
    }

    public function startSession(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial', 'create_serial')) abort(403);
        $doctorId = Auth::id();
        $today = now()->toDateString();
        if (SerialSession::where('doctor_id', $doctorId)->where('session_date', $today)->exists()) {
            return back()->with('error', 'Session already exists for today.');
        }
        SerialSession::create([
            'doctor_id' => $doctorId, 'session_date' => $today, 'session_label' => $request->input('label'),
            'status' => 'active', 'current_serial' => 0, 'total_patients' => 0, 'started_at' => now(),
        ]);
        return back()->with('success', 'Session started.');
    }

    public function closeSession(SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial')||$session->doctor_id!==Auth::id()) abort(403);
        $session->update(['status'=>'closed','closed_at'=>now()]);
        return back()->with('success','Session closed.');
    }

    public function pauseSession(SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial')||$session->doctor_id!==Auth::id()) abort(403);
        $session->update(['status'=>'paused']);
        return back()->with('success','Session paused.');
    }

    public function resumeSession(SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','edit_serial')||$session->doctor_id!==Auth::id()) abort(403);
        $session->update(['status'=>'active']);
        return back()->with('success','Session resumed.');
    }

    public function addPatient(Request $request)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','create_serial')) abort(403);
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

    public function callNext(SerialSession $session)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next')||$session->doctor_id!==Auth::id()) abort(403);
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

    public function callPatient($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        if ($q->status!=='waiting') return back()->with('error','Not waiting.');
        $q->update(['status'=>'called','called_at'=>now()]);
        return back()->with('success',"Called: {$q->patient->name}");
    }

    public function startConsultation($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','call_next')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['status'=>'in_consultation','consultation_started_at'=>now()]);
        return back()->with('success','Consultation started.');
    }

    public function complete($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','complete')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['status'=>'completed','completed_at'=>now()]);
        return back()->with('success','Completed.');
    }

    public function cancel($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','cancel_serial')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['status'=>'cancelled','cancelled_at'=>now()]);
        return back()->with('success','Cancelled.');
    }

    public function noShow($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','cancel_serial')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['status'=>'no_show']);
        return back()->with('success','Marked no-show.');
    }

    public function recall($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','recall')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        if (!in_array($q->status,['called','completed'])) return back()->with('error','Cannot recall.');
        $q->update(['status'=>'waiting','called_at'=>null,'consultation_started_at'=>null,'completed_at'=>null]);
        return back()->with('success','Recalled.');
    }

    public function skip($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','skip')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['status'=>'waiting','called_at'=>null]);
        $next = PatientQueue::where('serial_session_id',$q->serial_session_id)->where('status','waiting')->where('id','!=',$q->id)->orderBy('serial_number')->first();
        if ($next) $next->update(['status'=>'called','called_at'=>now()]);
        return back()->with('success','Skipped. Next called.');
    }

    public function emergency($queueId)
    {
        if (!Auth::user()->hasModulePermission('smart_serial','emergency')) abort(403);
        $q = PatientQueue::findOrFail($queueId);
        if ($q->doctor_id!==Auth::id()) abort(403);
        $q->update(['priority'=>'emergency']);
        if ($q->status==='waiting') {
            PatientQueue::where('serial_session_id',$q->serial_session_id)->where('status','called')->update(['status'=>'waiting','called_at'=>null]);
            $q->update(['status'=>'called','called_at'=>now()]);
        }
        return back()->with('success','Emergency set. Called immediately.');
    }

    public function queueStatus(SerialSession $session)
    {
        if ($session->doctor_id!==Auth::id()) return response()->json(['error'=>'Unauthorized'],403);
        $queue = $session->patientQueues()->with('patient')->orderBy('serial_number')->get();
        return response()->json(['session'=>$session->fresh(),'queue'=>$queue,'stats'=>[
            'total'=>$queue->count(),'waiting'=>$queue->where('status','waiting')->count(),
            'called'=>$queue->where('status','called')->count(),'in_consultation'=>$queue->where('status','in_consultation')->count(),
            'completed'=>$queue->where('status','completed')->count(),
        ]]);
    }
}
