<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleAddon extends Model
{
    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'description',
        'monthly_price',
        'quarterly_price',
        'semi_annual_price',
        'yearly_price',
        'lifetime_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'semi_annual_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'lifetime_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserAddonSubscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->userSubscriptions()->where('status', 'active');
    }

    /**
     * Get price for a specific billing cycle.
     */
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

    /**
     * Check if a user has an active subscription to this addon.
     */
    public function isSubscribed(User $user): bool
    {
        return $this->userSubscriptions()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    /**
     * Scope: only active addons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
