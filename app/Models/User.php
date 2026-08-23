<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 👉 Role system (admin/doctor)
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * =========================
     * MASS ASSIGNABLE FIELDS
     * =========================
     */
    protected $fillable = [
        'name', 'name_bn',
        'email',
        'password',
        'role',
        'is_approved',
        'tenant_id',
        'avatar',
        'phone', 'specialization', 'specialization_bn',
        'qualification', 'qualification_bn',
        'license_number', 'experience_years',
        'clinic_name', 'clinic_name_bn',
        'address', 'address_bn',
        'visiting_hours', 'status',
        'designation_title', 'designation_title_bn',
        'affiliated_hospital', 'affiliated_hospital_bn',
        'sub_specialties', 'sub_specialties_bn',
        'chambers', 'emergency_contact', 'emergency_contact_bn',
        'prescription_heading', 'prescription_heading_bn',
        'prescription_slogan', 'prescription_slogan_bn',
    ];

    /**
     * =========================
     * HIDDEN FIELDS (SECURITY)
     * =========================
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * =========================
     * TYPE CASTING
     * =========================
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sub_specialties' => 'array',
            'sub_specialties_bn' => 'array',
            'chambers' => 'array',
        ];
    }

    public function locale(string $field): string
    {
        $locale = app()->getLocale();
        if ($locale === 'bn') {
            $bnField = $field . '_bn';
            return $this->$bnField ?: $this->$field ?? '';
        }
        return $this->$field ?? '';
    }

    /**
     * =========================
     * RELATION: TENANT
     * =========================
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * =========================
     * RELATION: PATIENTS
     * =========================
     */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    /**
     * =========================
     * SUBSCRIPTION RELATIONS
     * =========================
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function prescriptionSetting()
    {
        return $this->hasOne(DoctorPrescriptionSetting::class, 'doctor_id');
    }

    public function smsSetting()
    {
        return $this->hasOne(DoctorSmsSetting::class, 'doctor_id');
    }

    public function doctorFeatureSetting()
    {
        return $this->hasOne(DoctorFeatureSetting::class, 'doctor_id');
    }

    /**
     * Get the modules enabled for this user.
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'user_modules')
            ->withPivot(['is_enabled', 'enabled_by', 'enabled_at', 'disabled_at'])
            ->withTimestamps();
    }

    /**
     * Get the user module pivot records for this user.
     */
    public function userModules()
    {
        return $this->hasMany(UserModule::class);
    }

    /**
     * Get the active modules enabled for this user.
     */
    public function activeModules()
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    /**
     * Get the module permissions for this user.
     */
    public function modulePermissions()
    {
        return $this->belongsToMany(ModulePermission::class, 'user_module_permissions')
            ->withPivot(['is_granted', 'granted_by', 'granted_at'])
            ->withTimestamps();
    }

    public function activePlan()
    {
        return $this->subscription?->plan;
    }

    /**
     * Check if user has an active (non-expired) subscription.
     */
    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscription;
        return $subscription && $subscription->isActive();
    }

    /**
     * Check if user's subscription has expired.
     */
    public function hasExpiredSubscription(): bool
    {
        $subscription = $this->subscription;
        if (!$subscription) {
            return false;
        }
        return $subscription->isExpired();
    }

    /**
     * Check if user's subscription is expiring soon (within days).
     */
    public function subscriptionExpiringSoon(int $days = 7): bool
    {
        $subscription = $this->subscription;
        if (!$subscription || !$subscription->ends_at) {
            return false;
        }
        return $subscription->ends_at->isFuture()
            && $subscription->ends_at->diffInDays(now()) <= $days;
    }

    /**
     * Get days until subscription expires.
     */
    public function subscriptionDaysUntilExpiry(): ?int
    {
        return $this->subscription?->daysUntilExpiry();
    }

    /**
     * Check if user has a specific feature on their active plan.
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->activePlan();
        if (!$plan) {
            return false;
        }

        $limitations = $plan->limitations ?? [];

        return match ($feature) {
            'ai_assistant' => ($limitations['ai_assistant'] ?? false) !== false,
            'analytics' => ($limitations['analytics'] ?? false) === true,
            'multi_doctor' => ($limitations['multi_doctor'] ?? false) === true,

            // Module-based features: check if value is not explicitly false
            'prescription', 'patient_management', 'appointment',
            'smart_serial', 'sms_notification', 'reports_analytics'
                => ($limitations[$feature] ?? false) !== false,

            default => true,
        };
    }

    /**
     * Check if user can access a specific module.
     */
    public function canAccessModule(string $moduleKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return app(\App\Services\ModuleAccessService::class)->canAccess($moduleKey, $this);
    }

    /**
     * Check if user has a specific permission for a module.
     */
    public function hasModulePermission(string $moduleSlug, string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return app(\App\Services\ModulePermissionService::class)->hasPermission($this, $moduleSlug, $permission);
    }

    /**
     * Get all permissions for a module.
     */
    public function getModulePermissions(string $moduleSlug): array
    {
        if ($this->isAdmin()) {
            return app(\App\Services\ModulePermissionService::class)
                ->getModulePermissions($moduleSlug)
                ->pluck('name')
                ->toArray();
        }

        return app(\App\Services\ModulePermissionService::class)
            ->getUserPermissions($this, $moduleSlug);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7c3aed&background=ede9fe';
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isDoctor()
    {
        return $this->hasRole('doctor');
    }

    public function isAssistant()
    {
        return $this->hasRole('assistant');
    }

    /**
     * Doctors this assistant is assigned to.
     */
    public function assignedDoctors()
    {
        return $this->belongsToMany(User::class, 'doctor_assistants', 'assistant_id', 'doctor_id')
            ->withTimestamps();
    }

    /**
     * Assistants assigned to this doctor.
     */
    public function assistants()
    {
        return $this->belongsToMany(User::class, 'doctor_assistants', 'doctor_id', 'assistant_id')
            ->withTimestamps();
    }

    /**
     * Get the doctor IDs this assistant can access.
     */
    public function getAccessibleDoctorIds(): array
    {
        if ($this->isDoctor()) {
            return [$this->id];
        }

        return $this->assignedDoctors()->pluck('users.id')->toArray();
    }
}
