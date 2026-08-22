<?php

namespace App\Notifications\Admin;

use App\Models\MedicineSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MedicineSuggestionCreated extends Notification
{
    use Queueable;

    public function __construct(public MedicineSuggestion $suggestion) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'medicine_suggestion',
            'suggestion_id' => $this->suggestion->id,
            'doctor_name' => $this->suggestion->doctor->name,
            'medicine_name' => $this->suggestion->name,
            'message' => "{$this->suggestion->doctor->name} suggested a new medicine: {$this->suggestion->name}.",
        ];
    }
}
