<?php

namespace App\Services;

use App\Models\DoctorSmsSetting;
use App\Models\SmsLog;

class SmsService
{
    public function send(DoctorSmsSetting $setting, string $phone, string $message, string $type = 'follow_up', ?int $patientId = null, ?int $prescriptionId = null): SmsLog
    {
        $log = SmsLog::create([
            'doctor_id' => $setting->doctor_id,
            'patient_id' => $patientId,
            'prescription_id' => $prescriptionId,
            'recipient_phone' => $phone,
            'message' => $message,
            'status' => 'pending',
            'type' => $type,
        ]);

        if (!$setting->sms_enabled || empty($setting->api_url)) {
            $log->update(['status' => 'failed', 'error_message' => 'SMS service disabled or API URL not configured']);
            return $log;
        }

        try {
            $response = $this->callApi($setting, $phone, $message);

            if ($response['success']) {
                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $this->truncateError($response['error'] ?? 'Unknown error'),
                ]);
            }
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $this->truncateError($e->getMessage()),
            ]);
        }

        return $log;
    }

    private function callApi(DoctorSmsSetting $setting, string $phone, string $message): array
    {
        $apiKey = $setting->api_key ?? '';
        $senderName = $setting->sender_id ?? '';
        $userName = $setting->username ?? '';

        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && $phone[0] === '0') {
            $phone = '88' . $phone;
        } elseif (strlen($phone) === 10) {
            $phone = '880' . $phone;
        }

        $payload = [
            'apiKey' => $apiKey,
            'userName' => $userName,
            'senderName' => $senderName,
            'campaignName' => $senderName ?: 'Default',
            'transactionType' => 'T',
            'mobileNumber' => $phone,
            'message' => $message,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $setting->api_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $response];
        }

        return ['success' => false, 'error' => "HTTP {$httpCode}: {$response}"];
    }

    public function replacePlaceholders(string $template, array $data): string
    {
        $replacements = [
            '{{patient_name}}' => $data['patient_name'] ?? '',
            '{{doctor_name}}' => $data['doctor_name'] ?? '',
            '{{followup_date}}' => $data['followup_date'] ?? '',
            '{{followup_time}}' => $data['followup_time'] ?? '',
            '{{prescription_number}}' => $data['prescription_number'] ?? '',
            '{{clinic_name}}' => $data['clinic_name'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function truncateError(string $message): string
    {
        return mb_substr($message, 0, 1000);
    }
}
