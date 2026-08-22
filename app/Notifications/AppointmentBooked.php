<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $patientName = $this->appointment->patient?->name ?? 'Unknown';
        $assistantName = $this->appointment->bookedBy?->name ?? 'Assistant';

        return [
            'appointment_id' => $this->appointment->id,
            'patient_name' => $patientName,
            'assistant_name' => $assistantName,
            'appointment_date' => $this->appointment->appointment_date?->toDateTimeString(),
            'message' => "{$assistantName} booked an appointment with {$patientName} on {$this->appointment->appointment_date?->format('M d, Y h:i A')}.",
        ];
    }
}
