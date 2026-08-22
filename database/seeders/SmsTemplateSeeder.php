<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Message',
                'type' => 'welcome',
                'body' => 'Dear {{patient_name}}, welcome to {{clinic_name}}! We are happy to have you. - Dr. {{doctor_name}}',
                'is_active' => true,
            ],
            [
                'name' => 'Follow-up Reminder',
                'type' => 'follow_up',
                'body' => 'Dear {{patient_name}}, this is a reminder for your follow-up visit. Please visit us soon. - Dr. {{doctor_name}}, {{clinic_name}}',
                'is_active' => true,
            ],
            [
                'name' => 'Appointment Reminder',
                'type' => 'appointment',
                'body' => 'Dear {{patient_name}}, you have an upcoming appointment on {{appointment_date}}. Reason: {{follow_up_reason}}. Please be on time. - Dr. {{doctor_name}}',
                'is_active' => true,
            ],
            [
                'name' => 'Custom Message',
                'type' => 'custom',
                'body' => 'Dear {{patient_name}}, this is a message from Dr. {{doctor_name}} at {{clinic_name}}.',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['name' => $template['name'], 'doctor_id' => null],
                $template
            );
        }
    }
}
