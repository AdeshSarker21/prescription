<?php

namespace App\Notifications\Admin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDoctorRegistration extends Notification
{
    use Queueable;

    public function __construct(public User $doctor) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'doctor_registration',
            'doctor_id' => $this->doctor->id,
            'doctor_name' => $this->doctor->name,
            'doctor_email' => $this->doctor->email,
            'message' => "New doctor registered: {$this->doctor->name} ({$this->doctor->email}).",
        ];
    }
}
