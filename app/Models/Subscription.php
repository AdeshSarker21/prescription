<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'status',
        'billing_cycle', 'starts_at', 'ends_at',
        'trial_ends_at', 'cancelled_at',
        'payment_method', 'transaction_id', 'sender_number',
        'payment_note', 'approved_at', 'approved_by',
        'amount_paid', 'renewed_from', 'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'approved_at' => 'datetime',
            'activated_at' => 'datetime',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function renewedFromSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'renewed_from');
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === 'active'
            && $this->ends_at !== null
            && $this->ends_at->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->status === 'active'
            && $this->ends_at !== null
            && $this->ends_at->isFuture()
            && $this->ends_at->diffInDays(now()) <= 7;
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->ends_at || $this->ends_at->isPast()) {
            return null;
        }

        return (int) now()->diffInDays($this->ends_at, false);
    }

    public function getBillingCycleLabel(): string
    {
        return match ($this->billing_cycle) {
            'monthly' => 'Monthly',
            'quarterly' => '3 Months',
            'semi_annual' => '6 Months',
            'yearly' => '12 Months',
            'lifetime' => 'Lifetime',
            default => ucfirst($this->billing_cycle),
        };
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_paid ?? ($this->plan ? $this->plan->getPriceForCycle($this->billing_cycle) : 0);
    }

    public function calculateEndDate(): Carbon
    {
        $start = $this->starts_at ?? Carbon::now();
        $days = $this->plan ? $this->plan->getDurationDays($this->billing_cycle) : 30;

        return $start->copy()->addDays($days);
    }
}
