<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMedicalHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'medical_history_condition_id',
        'condition_name',
        'diagnosed_date',
        'status',
        'notes',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function condition()
    {
        return $this->belongsTo(MedicalHistoryCondition::class, 'medical_history_condition_id');
    }
}
