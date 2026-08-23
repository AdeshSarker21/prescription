<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialStatusLog extends Model
{
    use HasFactory;

    protected $table = 'serial_status_logs';

    protected $fillable = [
        'patient_queue_id',
        'doctor_id',
        'serial_session_id',
        'formatted_serial',
        'old_status',
        'new_status',
        'notes',
        'changed_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUSES = [
        'waiting',
        'preparing',
        'calling',
        'inside',
        'completed',
        'skipped',
        'cancelled',
        'emergency',
    ];

    public function patientQueue()
    {
        return $this->belongsTo(PatientQueue::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function session()
    {
        return $this->belongsTo(SerialSession::class, 'serial_session_id');
    }
}
