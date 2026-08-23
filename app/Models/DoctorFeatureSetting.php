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
        'module_prescription',
        'module_patient_management',
        'module_appointment',
        'module_smart_serial',
        'module_sms_notification',
        'module_ai_assistant',
        'module_reports_analytics',
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
        'module_prescription' => 'boolean',
        'module_patient_management' => 'boolean',
        'module_appointment' => 'boolean',
        'module_smart_serial' => 'boolean',
        'module_sms_notification' => 'boolean',
        'module_ai_assistant' => 'boolean',
        'module_reports_analytics' => 'boolean',
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

    /**
     * Get all enabled modules for this doctor.
     */
    public function getEnabledModules(): array
    {
        $modules = [];
        foreach (self::ALL_MODULES as $module) {
            $column = 'module_' . $module;
            if ($this->$column) {
                $modules[] = $module;
            }
        }
        return $modules;
    }

    /**
     * Check if a specific module is enabled.
     */
    public function hasModule(string $moduleKey): bool
    {
        $column = 'module_' . $moduleKey;
        if (in_array($moduleKey, self::ALL_MODULES)) {
            return (bool) $this->$column;
        }
        return false;
    }

    /**
     * Enable or disable a module.
     */
    public function setModule(string $moduleKey, bool $enabled): bool
    {
        if (!in_array($moduleKey, self::ALL_MODULES)) {
            return false;
        }
        $column = 'module_' . $moduleKey;
        $this->update([$column => $enabled]);
        return true;
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

    /**
     * Get human-readable label for a module key.
     */
    public static function getModuleLabel(string $moduleKey): string
    {
        return match ($moduleKey) {
            'prescription' => 'Prescription',
            'patient_management' => 'Patient Management',
            'appointment' => 'Appointment',
            'smart_serial' => 'Smart Serial Management',
            'sms_notification' => 'SMS & Notification',
            'ai_assistant' => 'AI Assistant',
            'reports_analytics' => 'Reports & Analytics',
            default => $moduleKey,
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

    const ALL_MODULES = [
        'prescription',
        'patient_management',
        'appointment',
        'smart_serial',
        'sms_notification',
        'ai_assistant',
        'reports_analytics',
    ];
}
