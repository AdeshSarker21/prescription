<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Admin\NewSubscriptionPayment;
use App\Services\SubscriptionExpiryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionExpiryService $expiryService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $subscription = $user->subscription ?? $user->subscriptions()->with('plan')->latest()->first();

        $paymentHistory = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($sub) {
                $sub->amount = $sub->amount_paid ?? $sub->plan?->getPriceForCycle($sub->billing_cycle) ?? 0;
                $sub->plan_name = $sub->plan->name ?? 'N/A';
                return $sub;
            });

        $statusInfo = null;
        if ($subscription) {
            $statusInfo = $this->expiryService->getStatusInfo($subscription);
        }

        return view('doctor.subscription.index', compact('subscription', 'paymentHistory', 'statusInfo'));
    }

    public function plans(): View
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentPlanId = auth()->user()->subscription?->plan_id;

        return view('doctor.subscription.plans', compact('plans', 'currentPlanId'));
    }

    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        $user = auth()->user();
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
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($plan->getDurationDays($cycle)),
                'activated_at' => Carbon::now(),
                'amount_paid' => 0,
            ]);

            return redirect()->route('doctor.subscription')
                ->with('success', 'Subscribed to ' . $plan->name . ' plan successfully.');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_cycle' => $cycle,
            'amount_paid' => $plan->getPriceForCycle($cycle),
        ]);

        return redirect()->route('doctor.subscription.bkash-checkout', $subscription);
    }

    public function bkashCheckout(Subscription $subscription): View
    {
        // abort_if($subscription->user_id !== auth()->id(), 403);
        abort_if($subscription->status !== 'pending', 404);

        $amount = $subscription->plan->getPriceForCycle($subscription->billing_cycle);
        $paymentMethods = PaymentMethod::active()->orderBy('sort_order')->get();

        return view('doctor.subscription.bkash-checkout', compact('subscription', 'amount', 'paymentMethods'));
    }

    public function processPayment(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_if($subscription->user_id !== auth()->id(), 403);
        abort_if($subscription->status !== 'pending', 404);

        $data = $request->validate([
            'transaction_id' => 'required|string|max:255',
            'sender_number' => 'required|string|max:20',
            'payment_note' => 'nullable|string|max:1000',
        ]);

        $subscription->update([
            'payment_method' => 'bkash',
            'transaction_id' => $data['transaction_id'],
            'sender_number' => $data['sender_number'],
            'payment_note' => $data['payment_note'] ?? null,
        ]);

        User::role('admin')->each(fn ($admin) => $admin->notify(new NewSubscriptionPayment($subscription)));

        return redirect()->route('doctor.subscription.payment-confirmation', $subscription)
            ->with('success', 'Payment information submitted successfully. Please wait for admin approval.');
    }

    public function paymentConfirmation(Subscription $subscription): View
    {
        abort_if($subscription->user_id !== auth()->id(), 403);

        return view('doctor.subscription.payment-confirmation', compact('subscription'));
    }

    public function cancel(): RedirectResponse
    {
        $subscription = auth()->user()->subscription;

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('doctor.subscription')
            ->with('success', 'Subscription cancelled successfully.');
    }

    public function paymentHistory(): View
    {
        $payments = auth()->user()
            ->subscriptions()
            ->with('plan')
            ->latest()
            ->paginate(15)
            ->through(function ($sub) {
                $sub->amount = $sub->amount_paid ?? $sub->plan?->getPriceForCycle($sub->billing_cycle) ?? 0;
                $sub->plan_name = $sub->plan->name ?? 'N/A';
                return $sub;
            });

        return view('doctor.subscription.payment-history', compact('payments'));
    }
}
