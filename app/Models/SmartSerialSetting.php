<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartSerialSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'auto_call_next', 'show_in_appointment',
        'allow_priority', 'max_queue_size', 'serial_reset_daily', 'notification_enabled',
    ];

    protected $casts = [
        'auto_call_next' => 'boolean',
        'show_in_appointment' => 'boolean',
        'allow_priority' => 'boolean',
        'serial_reset_daily' => 'boolean',
        'notification_enabled' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
