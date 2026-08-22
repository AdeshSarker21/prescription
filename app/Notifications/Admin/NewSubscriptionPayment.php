<?php

namespace App\Notifications\Admin;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSubscriptionPayment extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_payment',
            'subscription_id' => $this->subscription->id,
            'doctor_name' => $this->subscription->user->name,
            'plan_name' => $this->subscription->plan->name,
            'transaction_id' => $this->subscription->transaction_id,
            'amount' => $this->subscription->billing_cycle === 'yearly'
                ? $this->subscription->plan->yearly_price
                : $this->subscription->plan->monthly_price,
            'message' => "{$this->subscription->user->name} submitted a bKash payment for {$this->subscription->plan->name}.",
        ];
    }
}
