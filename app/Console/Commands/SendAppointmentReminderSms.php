<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendAppointmentReminderSms extends Command
{
    protected $signature = 'sms:send-appointment-reminders';
    protected $description = 'Send appointment reminder SMS to patients with upcoming follow-ups';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->startOfDay();
        $endOfTomorrow = now()->addDay()->endOfDay();

        $followUps = FollowUp::with(['patient', 'doctor'])
            ->whereBetween('follow_up_date', [$tomorrow, $endOfTomorrow])
            ->where('reminder_sent', false)
            ->get();

        $sentCount = 0;

        foreach ($followUps as $followUp) {
            $doctor = $followUp->doctor;
            $patient = $followUp->patient;

            if (!$doctor->sms_setting || !$doctor->sms_setting->is_active || !$doctor->sms_setting->appointment_reminder_enabled) {
                continue;
            }

            if (empty($patient->phone)) continue;

            $template = SmsTemplate::where('type', 'appointment')
                ->where('doctor_id', $doctor->id)
                ->where('is_active', true)
                ->first();

            if (!$template) continue;

            $message = str_replace(
                [
                    '{{patient_name}}',
                    '{{doctor_name}}',
                    '{{clinic_name}}',
                    '{{appointment_date}}',
                    '{{follow_up_reason}}',
                ],
                [
                    $patient->name,
                    $doctor->name,
                    $doctor->clinic_name ?? '',
                    $followUp->follow_up_date->format('d M Y, h:i A'),
                    $followUp->reason ?? 'Follow-up',
                ],
                $template->body
            );

            SmsService::send($doctor->id, $patient->phone, $message, 'appointment', null, $patient->id);

            $followUp->update(['reminder_sent' => true]);
            $sentCount++;
        }

        $this->info("Sent {$sentCount} appointment reminder SMS.");
        return self::SUCCESS;
    }
}
