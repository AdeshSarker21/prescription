<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_session_id', 'doctor_id', 'patient_id', 'appointment_id',
        'serial_number', 'status', 'priority', 'notes',
        'called_at', 'consultation_started_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
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
}
