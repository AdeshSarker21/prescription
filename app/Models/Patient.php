<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Patient extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_number',
        'name',
        'email',
        'phone',
        'emergency_contact',
        'date_of_birth',
        'gender',
        'blood_group',
        'height',
        'weight',
        'occupation',
        'marital_status',
        'national_id',
        'address',
        'medical_history',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    public function medicalHistories(): HasMany
    {
        return $this->hasMany(PatientMedicalHistory::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function testReports(): HasManyThrough
    {
        return $this->hasManyThrough(
            PrescriptionTestReport::class,
            Prescription::class,
            'patient_id',
            'prescription_id',
            'id',
            'id'
        );
    }
}
