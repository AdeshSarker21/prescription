<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'chamber_id', 'session_date', 'session_label', 'status',
        'current_serial', 'total_patients', 'daily_serial_counter',
        'started_at', 'closed_at',
    ];

    protected $casts = [
        'session_date' => 'date',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function chamber()
    {
        return $this->belongsTo(SmartSerialChamber::class, 'chamber_id');
    }

    public function patientQueues()
    {
        return $this->hasMany(PatientQueue::class, 'serial_session_id');
    }

    /**
     * Generate the next formatted serial number for this session.
     * Ensures uniqueness within Doctor + Chamber + Date.
     */
    public function generateNextSerial(SmartSerialSetting $settings): string
    {
        $this->increment('daily_serial_counter');
        $serial = $this->daily_serial_counter;
        $this->update(['current_serial' => $serial]);

        $prefix = $settings->getEffectivePrefix($this->chamber);
        $padding = max(3, strlen((string) $settings->max_serial));
        $formatted = str_pad($serial, $padding, '0', STR_PAD_LEFT);

        return $prefix ? "{$prefix}-{$formatted}" : $formatted;
    }

    /**
     * Check if a formatted serial already exists in this session (duplicate prevention).
     */
    public function serialExists(string $formattedSerial): bool
    {
        return $this->patientQueues()
            ->where('formatted_serial', $formattedSerial)
            ->exists();
    }
}
