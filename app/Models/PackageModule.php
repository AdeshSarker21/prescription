<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageModule extends Model
{
    protected $table = 'package_modules';

    protected $fillable = [
        'plan_id',
        'module_id',
        'is_included',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'is_included' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Scope: only included entries.
     */
    public function scopeIncluded($query)
    {
        return $query->where('is_included', true);
    }

    /**
     * Scope: for a specific plan.
     */
    public function scopeForPlan($query, int $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * Scope: for a specific module.
     */
    public function scopeForModule($query, int $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope: ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
