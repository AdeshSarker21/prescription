<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionExpiryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionExpiryService $expiryService
    ) {}

    public function plans()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentPlan = auth()->user()->activePlan();

        return view('subscription.plans', compact('plans', 'currentPlan'));
    }

    public function subscribe(Request $request, Plan $plan)
    {
        $user = $request->user();

        if (!$plan->is_active) {
            return back()->with('error', 'This plan is not available.');
        }

        $cycle = $request->input('billing_cycle', 'monthly');

        $validCycles = array_keys(Plan::billingCycles());
        if (!in_array($cycle, $validCycles)) {
            $cycle = 'monthly';
        }

        if ($plan->isFree()) {
            $existing = $user->subscriptions()
                ->where('status', 'active')
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => 'cancelled',
                    'cancelled_at' => Carbon::now(),
                ]);
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $cycle,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->getDurationDays($cycle)),
                'activated_at' => now(),
                'amount_paid' => 0,
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'You have subscribed to the ' . $plan->name . ' plan.');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_cycle' => $cycle,
            'amount_paid' => $plan->getPriceForCycle($cycle),
        ]);

        return redirect()->route('subscription.checkout', ['plan' => $plan->id, 'billing_cycle' => $cycle]);
    }

    public function checkout(Plan $plan)
    {
        $cycle = request('billing_cycle', 'monthly');
        $price = $plan->getPriceForCycle($cycle);

        if ($plan->isFree()) {
            return redirect()->route('subscription.plans');
        }

        return view('subscription.checkout', compact('plan', 'cycle', 'price'));
    }

    public function paymentSuccess(Request $request)
    {
        $subscription = Subscription::find($request->subscription_id);

        if ($subscription) {
            $this->expiryService->activate($subscription);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Payment successful! Your subscription is now active.');
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Subscription cancelled successfully.');
    }
}
