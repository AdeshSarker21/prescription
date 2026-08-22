<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSmsSetting;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Http\Request;

class DoctorSmsSettingController extends Controller
{
    public function index()
    {
        $doctors = User::role('doctor')->with('smsSetting')->orderBy('name')->paginate(20);
        return view('admin.sms-settings.index', compact('doctors'));
    }

    public function edit(int $doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $setting = DoctorSmsSetting::getForDoctor($doctorId);
        return view('admin.sms-settings.edit', compact('doctor', 'setting'));
    }

    public function update(Request $request, int $doctorId)
    {
        $setting = DoctorSmsSetting::getForDoctor($doctorId);

        $data = $request->validate([
            'sms_enabled' => 'nullable|in:1',
            'api_url' => 'required|string|max:500',
            'api_key' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'reminder_days_before' => 'required|integer|min:1|max:30',
            'sms_template' => 'nullable|string',
        ]);

        $setting->update([
            'sms_enabled' => isset($data['sms_enabled']),
            'api_url' => $data['api_url'],
            'api_key' => $data['api_key'] ?? null,
            'sender_id' => $data['sender_id'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
            'reminder_days_before' => $data['reminder_days_before'],
            'sms_template' => $data['sms_template'] ?? $setting->getDefaultTemplate(),
        ]);

        return back()->with('success', 'SMS settings updated successfully.');
    }

    public function logs(Request $request)
    {
        $query = SmsLog::with(['doctor', 'patient', 'prescription']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($doctorId = $request->input('doctor_id')) {
            $query->where('doctor_id', $doctorId);
        }

        $logs = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $doctors = User::role('doctor')->orderBy('name')->get();

        return view('admin.sms-settings.logs', compact('logs', 'doctors'));
    }

    public function toggleDoctorSms(int $doctorId)
    {
        $setting = DoctorSmsSetting::getForDoctor($doctorId);
        $setting->update(['sms_enabled' => !$setting->sms_enabled]);

        return back()->with('success', 'SMS service ' . ($setting->sms_enabled ? 'enabled' : 'disabled') . ' for doctor.');
    }

    public function testSms(int $doctorId)
    {
        $setting = DoctorSmsSetting::getForDoctor($doctorId);

        if (!$setting->sms_enabled || empty($setting->api_url)) {
            return back()->with('error', 'SMS service is not enabled or API URL is empty.');
        }

        $service = new \App\Services\SmsService();
        $template = $setting->getDefaultTemplate();
        $message = $service->replacePlaceholders($template, [
            'patient_name' => 'Test Patient',
            'doctor_name' => $setting->doctor->name,
            'followup_date' => now()->addDays($setting->reminder_days_before)->format('d/m/Y'),
            'followup_time' => '10:00 AM',
        ]);

        $log = $service->send($setting, '01700000000', $message, 'test');

        if ($log->status === 'sent') {
            return back()->with('success', 'Test SMS sent successfully!');
        }

        return back()->with('error', 'Test SMS failed: ' . ($log->error_message ?? 'Unknown error'));
    }

    public function templates()
    {
        $templates = SmsTemplate::whereNull('doctor_id')->orderBy('type')->orderBy('name')->get();
        return view('admin.sms-settings.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,follow_up,appointment,custom',
            'body' => 'required|string|max:1000',
            'is_active' => 'nullable|in:1',
        ]);

        $data['is_active'] = isset($data['is_active']);
        $data['doctor_id'] = null;

        SmsTemplate::create($data);

        return back()->with('success', 'Template created successfully.');
    }

    public function updateTemplate(Request $request, int $id)
    {
        $template = SmsTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,follow_up,appointment,custom',
            'body' => 'required|string|max:1000',
            'is_active' => 'nullable|in:1',
        ]);

        $data['is_active'] = isset($data['is_active']);

        $template->update($data);

        return back()->with('success', 'Template updated successfully.');
    }

    public function destroyTemplate(int $id)
    {
        SmsTemplate::whereNull('doctor_id')->findOrFail($id)->delete();
        return back()->with('success', 'Template deleted.');
    }
}
