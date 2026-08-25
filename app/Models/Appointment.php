<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'status',
        'reason',
        'notes',
        'booked_by',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function patientQueue()
    {
        return $this->hasOne(PatientQueue::class, 'appointment_id');
    }
}
