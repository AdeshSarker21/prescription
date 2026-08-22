<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnosis extends Model
{
    protected $fillable = [
        'patient_id',
        'prescription_id',
        'doctor_id',
        'diagnosis',
        'icd_code',
        'type',
        'diagnosed_date',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
