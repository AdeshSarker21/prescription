<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiring;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionExpiryService
{
    /**
     * Automatically calculate and set the expiry date based on billing cycle.
     */
    public function calculateEndDate(Subscription $subscription): Carbon
    {
        $start = $subscription->starts_at ?? Carbon::now();
        $days = $subscription->plan
            ? $subscription->plan->getDurationDays($subscription->billing_cycle)
            : 30;

        return $start->copy()->addDays($days);
    }

    /**
     * Activate a subscription and set proper dates.
     */
    public function activate(Subscription $subscription, ?int $approvedBy = null): Subscription
    {
        $now = Carbon::now();

        // Cancel any other active subscriptions for this user
        $subscription->user->subscriptions()
            ->where('status', 'active')
            ->where('id', '!=', $subscription->id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $now,
            ]);

        $subscription->update([
            'status' => 'active',
            'starts_at' => $now,
            'ends_at' => $this->calculateEndDate($subscription),
            'activated_at' => $now,
            'approved_at' => $now,
            'approved_by' => $approvedBy,
        ]);

        return $subscription->fresh();
    }

    /**
     * Renew a subscription (create a new one based on the old one).
     */
    public function renew(Subscription $oldSubscription, ?float $amountPaid = null): Subscription
    {
        $newSubscription = Subscription::create([
            'user_id' => $oldSubscription->user_id,
            'plan_id' => $oldSubscription->plan_id,
            'status' => 'pending',
            'billing_cycle' => $oldSubscription->billing_cycle,
            'renewed_from' => $oldSubscription->id,
            'amount_paid' => $amountPaid,
        ]);

        return $newSubscription;
    }

    /**
     * Expire subscriptions that have passed their end date.
     */
    public function expireOverdueSubscriptions(): int
    {
        $expired = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        if ($expired > 0) {
            Log::info("Expired {$expired} overdue subscriptions.");
        }

        return $expired;
    }

    /**
     * Send reminder notifications for subscriptions expiring at milestones.
     * Checks: 7 days, 3 days, 1 day before expiry.
     */
    public function sendExpiryReminders(): int
    {
        $reminderDays = [7, 3, 1];
        $sent = 0;

        Subscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', Carbon::now())
            ->chunk(100, function ($subscriptions) use ($reminderDays, &$sent) {
                foreach ($subscriptions as $sub) {
                    $daysRemaining = (int) Carbon::now()->diffInDays($sub->ends_at, false);

                    if (in_array($daysRemaining, $reminderDays)) {
                        $sub->user->notify(new SubscriptionExpiring($sub, $daysRemaining));
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    /**
     * Get subscription status for display.
     */
    public function getStatusInfo(Subscription $subscription): array
    {
        if ($subscription->status === 'expired' || $subscription->isExpired()) {
            return [
                'status' => 'expired',
                'label' => 'Expired',
                'color' => 'red',
                'message' => 'Your subscription has expired. Please renew to continue.',
            ];
        }

        if ($subscription->status === 'cancelled') {
            return [
                'status' => 'cancelled',
                'label' => 'Cancelled',
                'color' => 'gray',
                'message' => 'Your subscription has been cancelled.',
            ];
        }

        if ($subscription->status === 'pending') {
            return [
                'status' => 'pending',
                'label' => 'Pending Approval',
                'color' => 'yellow',
                'message' => 'Your payment is awaiting admin approval.',
            ];
        }

        if ($subscription->isExpiringSoon()) {
            $days = $subscription->daysUntilExpiry();
            return [
                'status' => 'expiring',
                'label' => 'Expiring Soon',
                'color' => 'orange',
                'message' => "Your subscription expires in {$days} day(s).",
                'days_remaining' => $days,
            ];
        }

        return [
            'status' => 'active',
            'label' => 'Active',
            'color' => 'green',
            'message' => 'Your subscription is active.',
        ];
    }

    /**
     * Check if a user can access a specific feature.
     */
    public function canAccessFeature(User $user, string $feature): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->hasActiveSubscription()) {
            return false;
        }

        return $user->hasFeature($feature);
    }
}
