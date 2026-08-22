<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'ends_at' => $this->subscription->ends_at?->toDateString(),
            'days_remaining' => $this->daysRemaining,
            'message' => $this->daysRemaining <= 0
                ? "Your {$this->subscription->plan->name} plan has expired."
                : "Your {$this->subscription->plan->name} plan expires in {$this->daysRemaining} day(s).",
        ];
    }
}
