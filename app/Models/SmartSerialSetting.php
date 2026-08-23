<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartSerialSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'auto_call_next',
        'show_in_appointment',
        'allow_priority',
        'max_queue_size',
        'serial_reset_daily',
        'notification_enabled',
        'starting_serial_number',
        'auto_increment',
        'prefix',
        'max_serial',
        'emergency_priority',
        'queue_mode',
        'voice_enabled',
        'display_enabled',
    ];

    protected $casts = [
        'auto_call_next' => 'boolean',
        'show_in_appointment' => 'boolean',
        'allow_priority' => 'boolean',
        'serial_reset_daily' => 'boolean',
        'notification_enabled' => 'boolean',
        'auto_increment' => 'boolean',
        'emergency_priority' => 'boolean',
        'voice_enabled' => 'boolean',
        'display_enabled' => 'boolean',
        'starting_serial_number' => 'integer',
        'max_serial' => 'integer',
        'max_queue_size' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function getEffectiveStartingSerial(?SmartSerialChamber $chamber = null): int
    {
        if ($chamber && $chamber->daily_starting_number > 0) {
            return $chamber->daily_starting_number;
        }
        return $this->starting_serial_number;
    }

    public function getEffectivePrefix(?SmartSerialChamber $chamber = null): string
    {
        if ($chamber && $chamber->serial_prefix) {
            return $chamber->serial_prefix;
        }
        return $this->prefix;
    }
}
