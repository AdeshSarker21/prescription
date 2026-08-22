<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public string $reminderType, // 'today' or 'upcoming'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $patientName = $this->appointment->patient?->name ?? 'Unknown';

        return [
            'appointment_id' => $this->appointment->id,
            'patient_name' => $patientName,
            'appointment_date' => $this->appointment->appointment_date?->toDateTimeString(),
            'reminder_type' => $this->reminderType,
            'message' => $this->reminderType === 'today'
                ? "You have an appointment with {$patientName} today."
                : "Reminder: Appointment with {$patientName} on {$this->appointment->appointment_date?->format('M d, Y h:i A')}.",
        ];
    }
}
