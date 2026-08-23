<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientQueue extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_CALLING = 'calling';
    public const STATUS_INSIDE = 'inside';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EMERGENCY = 'emergency';

    public const VALID_STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_PREPARING,
        self::STATUS_CALLING,
        self::STATUS_INSIDE,
        self::STATUS_COMPLETED,
        self::STATUS_SKIPPED,
        self::STATUS_CANCELLED,
        self::STATUS_EMERGENCY,
    ];

    protected $fillable = [
        'serial_session_id', 'doctor_id', 'patient_id', 'appointment_id',
        'serial_number', 'formatted_serial', 'status', 'priority', 'notes',
        'called_at', 'prepared_at', 'entered_at', 'consultation_started_at',
        'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'prepared_at' => 'datetime',
        'entered_at' => 'datetime',
        'consultation_started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(SerialSession::class, 'serial_session_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(SerialStatusLog::class, 'patient_queue_id');
    }

    /**
     * Log a status change without overwriting historical records.
     */
    public function logStatusChange(string $newStatus, ?string $changedBy = null, ?string $notes = null): SerialStatusLog
    {
        $oldStatus = $this->status;

        return SerialStatusLog::create([
            'patient_queue_id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'serial_session_id' => $this->serial_session_id,
            'formatted_serial' => $this->formatted_serial,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy,
        ]);
    }

    /**
     * Transition to a new status with timestamp tracking and history logging.
     */
    public function transitionTo(string $newStatus, ?string $changedBy = null, ?string $notes = null): bool
    {
        if (!in_array($newStatus, self::VALID_STATUSES)) {
            return false;
        }

        $now = now();
        $updates = ['status' => $newStatus];

        // Set the appropriate timestamp for the new status
        switch ($newStatus) {
            case self::STATUS_PREPARING:
                $updates['prepared_at'] = $now;
                break;
            case self::STATUS_CALLING:
                $updates['called_at'] = $now;
                break;
            case self::STATUS_INSIDE:
                $updates['entered_at'] = $now;
                $updates['consultation_started_at'] = $now;
                break;
            case self::STATUS_COMPLETED:
                $updates['completed_at'] = $now;
                break;
            case self::STATUS_CANCELLED:
                $updates['cancelled_at'] = $now;
                break;
        }

        $this->update($updates);
        $this->logStatusChange($newStatus, $changedBy, $notes);

        return true;
    }

    /**
     * Get the display-friendly formatted serial (e.g., "001", "A-001").
     */
    public function getDisplaySerialAttribute(): string
    {
        return $this->formatted_serial ?? str_pad($this->serial_number, 3, '0', STR_PAD_LEFT);
    }
}
