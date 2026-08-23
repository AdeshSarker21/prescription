<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'session_date', 'session_label', 'status',
        'current_serial', 'total_patients', 'started_at', 'closed_at',
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

    public function patientQueues()
    {
        return $this->hasMany(PatientQueue::class, 'serial_session_id');
    }
}
