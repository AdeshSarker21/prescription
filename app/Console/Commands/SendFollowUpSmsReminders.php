<?php

namespace App\Console\Commands;

use App\Models\DoctorSmsSetting;
use App\Models\Prescription;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendFollowUpSmsReminders extends Command
{
    protected $signature = 'sms:send-follow-up-reminders';
    protected $description = 'Send SMS reminders to patients before their follow-up date';

    public function handle(SmsService $smsService): int
    {
        $doctorSettings = DoctorSmsSetting::enabled()->get();
        $totalSent = 0;
        $totalFailed = 0;

        foreach ($doctorSettings as $setting) {
            $daysBefore = $setting->reminder_days_before;
            $targetDate = now()->addDays($daysBefore)->startOfDay();
            $targetDateEnd = $targetDate->copy()->endOfDay();

            $prescriptions = Prescription::where('doctor_id', $setting->doctor_id)
                ->whereNotNull('follow_up_date')
                ->whereBetween('follow_up_date', [$targetDate, $targetDateEnd])
                ->with(['patient', 'doctor'])
                ->get();

            foreach ($prescriptions as $prescription) {
                $patient = $prescription->patient;
                if (!$patient || empty($patient->phone)) {
                    continue;
                }

                $alreadySent = SmsLog::where('doctor_id', $setting->doctor_id)
                    ->where('prescription_id', $prescription->id)
                    ->where('type', 'follow_up')
                    ->where('status', 'sent')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $template = $setting->getDefaultTemplate();
                $message = $smsService->replacePlaceholders($template, [
                    'patient_name' => $patient->name,
                    'doctor_name' => $setting->doctor->name,
                    'followup_date' => \Carbon\Carbon::parse($prescription->follow_up_date)->format('d/m/Y'),
                    'followup_time' => $prescription->follow_up_time ?? 'সুনির্দিষ্ট সময়ে',
                    'prescription_number' => $prescription->prescription_number,
                ]);

                $log = $smsService->send(
                    $setting,
                    $patient->phone,
                    $message,
                    'follow_up',
                    $patient->id,
                    $prescription->id
                );

                if ($log->status === 'sent') {
                    $totalSent++;
                } else {
                    $totalFailed++;
                }
            }
        }

        $this->info("SMS Reminders: {$totalSent} sent, {$totalFailed} failed.");

        return Command::SUCCESS;
    }
}
