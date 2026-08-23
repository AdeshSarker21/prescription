<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'monthly_price', 'yearly_price',
        'quarterly_price', 'semi_annual_price', 'lifetime_price',
        'max_patients', 'features', 'limitations',
        'is_active', 'is_popular', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'quarterly_price' => 'decimal:2',
            'semi_annual_price' => 'decimal:2',
            'lifetime_price' => 'decimal:2',
            'max_patients' => 'integer',
            'features' => 'array',
            'limitations' => 'array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the modules included in this plan.
     */
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'package_modules')
            ->withPivot(['is_included', 'settings', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Get the active modules included in this plan.
     */
    public function includedModules()
    {
        return $this->modules()->wherePivot('is_included', true);
    }

    /**
     * Check if this plan includes a specific module.
     */
    public function includesModule(string $moduleSlug): bool
    {
        return $this->modules()
            ->where('modules.slug', $moduleSlug)
            ->wherePivot('is_included', true)
            ->exists();
    }

    public function isFree(): bool
    {
        return $this->monthly_price <= 0
            && $this->yearly_price <= 0
            && $this->quarterly_price <= 0
            && $this->semi_annual_price <= 0
            && $this->lifetime_price <= 0;
    }

    public function hasUnlimitedPatients(): bool
    {
        return is_null($this->max_patients);
    }

    public function getPriceForCycle(string $cycle): float
    {
        return match ($cycle) {
            'monthly' => (float) $this->monthly_price,
            'quarterly' => (float) $this->quarterly_price,
            'semi_annual' => (float) $this->semi_annual_price,
            'yearly' => (float) $this->yearly_price,
            'lifetime' => (float) $this->lifetime_price,
            default => (float) $this->monthly_price,
        };
    }

    public function getDurationDays(string $cycle): int
    {
        return match ($cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi_annual' => 180,
            'yearly' => 365,
            'lifetime' => 36500, // ~100 years
            default => 30,
        };
    }

    public static function billingCycles(): array
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => '3 Months',
            'semi_annual' => '6 Months',
            'yearly' => '12 Months',
            'lifetime' => 'Lifetime',
        ];
    }
}
