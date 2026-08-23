<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'is_active',
        'is_core',
        'route_prefix',
        'icon',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the permissions for this module.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(ModulePermission::class);
    }

    /**
     * Get the users this module is enabled for.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_modules')
            ->withPivot(['is_enabled', 'enabled_by', 'enabled_at', 'disabled_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the active users this module is enabled for.
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_enabled', true);
    }

    /**
     * Get the plans that include this module.
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'package_modules')
            ->withPivot(['is_included', 'settings', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * Get the plans that include this module (active only).
     */
    public function includedPlans(): BelongsToMany
    {
        return $this->plans()->wherePivot('is_included', true);
    }

    /**
     * Check if the module is enabled for a specific user.
     */
    public function isEnabledForUser(int $userId): bool
    {
        if ($this->is_core) {
            return true;
        }

        return $this->users()
            ->where('user_id', $userId)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Check if a plan includes this module.
     */
    public function isIncludedInPlan(int $planId): bool
    {
        return $this->plans()
            ->where('plan_id', $planId)
            ->wherePivot('is_included', true)
            ->exists();
    }

    /**
     * Scope: only active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only core modules.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope: only optional (non-core) modules.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_core', false);
    }

    /**
     * Scope: ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
