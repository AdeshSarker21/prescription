<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorSmsSetting;
use App\Models\Patient;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsCenterController extends Controller
{
    public function index()
    {
        $doctorId = auth()->id();
        $setting = DoctorSmsSetting::where('doctor_id', $doctorId)->first();

        $stats = [
            'total' => SmsLog::where('doctor_id', $doctorId)->count(),
            'sent' => SmsLog::where('doctor_id', $doctorId)->where('status', 'sent')->count(),
            'failed' => SmsLog::where('doctor_id', $doctorId)->where('status', 'failed')->count(),
            'pending' => SmsLog::where('doctor_id', $doctorId)->where('status', 'pending')->count(),
        ];

        $recentLogs = SmsLog::where('doctor_id', $doctorId)
            ->with('patient')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('doctor.sms-center.index', compact('setting', 'stats', 'recentLogs'));
    }

    public function sendForm()
    {
        $doctorId = auth()->id();
        $setting = DoctorSmsSetting::where('doctor_id', $doctorId)->first();
        $patients = Patient::where('doctor_id', $doctorId)->orderBy('name')->get();
        $templates = SmsTemplate::active()->forDoctor($doctorId)->orderBy('name')->get();

        return view('doctor.sms-center.send', compact('setting', 'patients', 'templates'));
    }

    public function send(Request $request, SmsService $smsService)
    {
        $doctorId = auth()->id();
        $setting = DoctorSmsSetting::where('doctor_id', $doctorId)->first();

        if (!$setting || !$setting->sms_enabled) {
            return back()->with('error', 'SMS service is not enabled for your account.');
        }

        $data = $request->validate([
            'patient_ids' => 'required|array|min:1',
            'patient_ids.*' => 'exists:patients,id',
            'message' => 'required|string|max:1000',
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($data['patient_ids'] as $patientId) {
            $patient = Patient::find($patientId);
            if (!$patient || empty($patient->phone)) {
                $failedCount++;
                continue;
            }

            $message = $smsService->replacePlaceholders($data['message'], [
                'patient_name' => $patient->name,
                'doctor_name' => auth()->user()->name,
                'followup_date' => '',
                'followup_time' => '',
            ]);

            $log = $smsService->send($setting, $patient->phone, $message, 'custom', $patient->id);

            if ($log->status === 'sent') {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        $message = "SMS sent: {$sentCount} successful";
        if ($failedCount > 0) {
            $message .= ", {$failedCount} failed";
        }

        return back()->with('success', $message);
    }

    public function logs(Request $request)
    {
        $doctorId = auth()->id();
        $query = SmsLog::where('doctor_id', $doctorId)->with('patient');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $logs = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('doctor.sms-center.logs', compact('logs'));
    }

    public function templates()
    {
        $doctorId = auth()->id();
        $templates = SmsTemplate::forDoctor($doctorId)->orderByDesc('id')->paginate(20);
        return view('doctor.sms-center.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,follow_up,appointment,custom',
            'message' => 'required|string|max:1000',
        ]);

        SmsTemplate::create([
            'doctor_id' => auth()->id(),
            'name' => $data['name'],
            'type' => $data['type'],
            'message' => $data['message'],
        ]);

        return back()->with('success', 'Template created successfully.');
    }

    public function updateTemplate(Request $request, int $id)
    {
        $template = SmsTemplate::where('doctor_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,follow_up,appointment,custom',
            'message' => 'required|string|max:1000',
            'is_active' => 'nullable|in:1',
        ]);

        $template->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'message' => $data['message'],
            'is_active' => isset($data['is_active']),
        ]);

        return back()->with('success', 'Template updated successfully.');
    }

    public function destroyTemplate(int $id)
    {
        $template = SmsTemplate::where('doctor_id', auth()->id())->findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }
}
