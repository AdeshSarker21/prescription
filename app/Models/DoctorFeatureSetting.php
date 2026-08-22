<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorFeatureSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'complaints',
        'tests',
        'medical_history',
        'advice',
        'clinical_features',
        'family_history',
        'menstrual_history',
        'drug_history',
        'ot_note',
        'anesthesia',
        'procedure',
        'treatment_plan',
    ];

    protected $casts = [
        'complaints' => 'boolean',
        'tests' => 'boolean',
        'medical_history' => 'boolean',
        'advice' => 'boolean',
        'clinical_features' => 'boolean',
        'family_history' => 'boolean',
        'menstrual_history' => 'boolean',
        'drug_history' => 'boolean',
        'ot_note' => 'boolean',
        'anesthesia' => 'boolean',
        'procedure' => 'boolean',
        'treatment_plan' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public static function getForDoctor(int $doctorId): self
    {
        return static::firstOrCreate(['doctor_id' => $doctorId]);
    }

    public function getEnabledFeatures(): array
    {
        $features = [];
        foreach (self::ALL_FEATURES as $feature) {
            if ($this->$feature) {
                $features[] = $feature;
            }
        }
        return $features;
    }

    public static function getFeatureLabel(string $key): string
    {
        return match ($key) {
            'complaints' => 'Complaints',
            'tests' => 'Tests',
            'medical_history' => 'Past Medical History',
            'advice' => 'Advice',
            'clinical_features' => 'Clinical Features',
            'family_history' => 'Family History',
            'menstrual_history' => 'Menstrual History',
            'drug_history' => 'Drug History',
            'ot_note' => 'OT Note / Procedure Done',
            'anesthesia' => 'Anesthesia (LA / Surface)',
            'procedure' => 'Procedure',
            'treatment_plan' => 'Treatment Plan',
            default => $key,
        };
    }

    const ALL_FEATURES = [
        'complaints',
        'tests',
        'medical_history',
        'advice',
        'clinical_features',
        'family_history',
        'menstrual_history',
        'drug_history',
        'ot_note',
        'anesthesia',
        'procedure',
        'treatment_plan',
    ];
}
