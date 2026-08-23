<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddonSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'module_addon_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'amount_paid',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moduleAddon(): BelongsTo
    {
        return $this->belongsTo(ModuleAddon::class);
    }

    /**
     * Check if this subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if this subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'active'
            && $this->ends_at !== null
            && $this->ends_at->isPast();
    }

    /**
     * Calculate the end date based on billing cycle.
     */
    public function calculateEndDate(): \Carbon\Carbon
    {
        $addon = $this->moduleAddon;
        $durationDays = match ($this->billing_cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi_annual' => 180,
            'yearly' => 365,
            'lifetime' => 36500,
            default => 30,
        };

        return now()->addDays($durationDays);
    }

    /**
     * Get billing cycle label.
     */
    public function getBillingCycleLabel(): string
    {
        return match ($this->billing_cycle) {
            'monthly' => 'Monthly',
            'quarterly' => '3 Months',
            'semi_annual' => '6 Months',
            'yearly' => '12 Months',
            'lifetime' => 'Lifetime',
            default => 'Monthly',
        };
    }

    /**
     * Scope: only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
