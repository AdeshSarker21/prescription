<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ModuleAddon;
use App\Models\UserAddonSubscription;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    /**
     * Show available add-ons for the doctor.
     */
    public function index()
    {
        $user = auth()->user();
        $addons = ModuleAddon::active()->ordered()->with('module')->get();

        $activeAddonIds = $user->activeAddonSubscriptions()
            ->pluck('module_addon_id')
            ->toArray();

        return view('doctor.addons.index', compact('addons', 'activeAddonIds'));
    }

    /**
     * Purchase an add-on (creates a pending subscription).
     */
    public function purchase(Request $request, ModuleAddon $addon)
    {
        $user = auth()->user();

        if (!$addon->is_active) {
            return back()->with('error', 'This add-on is not available.');
        }

        if ($addon->isSubscribed($user)) {
            return back()->with('error', 'You already have an active subscription to this add-on.');
        }

        $request->validate([
            'billing_cycle' => 'required|string|in:monthly,quarterly,semi_annual,yearly,lifetime',
        ]);

        $amount = $addon->getPriceForCycle($request->billing_cycle);

        // Cancel any existing subscription for this addon
        UserAddonSubscription::where('user_id', $user->id)
            ->where('module_addon_id', $addon->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $subscription = UserAddonSubscription::create([
            'user_id' => $user->id,
            'module_addon_id' => $addon->id,
            'status' => $amount > 0 ? 'pending' : 'active',
            'billing_cycle' => $request->billing_cycle,
            'starts_at' => $amount > 0 ? null : now(),
            'ends_at' => $amount > 0 ? null : now()->addDays($addon->getDurationDays($request->billing_cycle)),
            'amount_paid' => $amount,
        ]);

        if ($amount > 0) {
            return redirect()->route('doctor.addons.checkout', $subscription)
                ->with('info', 'Please complete payment to activate this add-on.');
        }

        return back()->with('success', "Add-on '{$addon->name}' activated successfully!");
    }

    /**
     * Show checkout page for an add-on purchase.
     */
    public function checkout(UserAddonSubscription $subscription)
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        if ($subscription->status !== 'pending') {
            return redirect()->route('doctor.addons.index')
                ->with('error', 'This subscription is not pending payment.');
        }

        return view('doctor.addons.checkout', compact('subscription'));
    }

    /**
     * Process payment for an add-on (bKash-style).
     */
    public function processPayment(Request $request, UserAddonSubscription $subscription)
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'sender_number' => 'required|string|max:20',
            'payment_note' => 'nullable|string|max:500',
        ]);

        $subscription->update([
            'payment_method' => 'bkash',
            'transaction_id' => $request->transaction_id,
        ]);

        // Notify admins
        // \App\Notifications\Admin\NewAddonPayment::dispatch($subscription);

        return redirect()->route('doctor.addons.confirmation', $subscription);
    }

    /**
     * Show payment confirmation page.
     */
    public function confirmation(UserAddonSubscription $subscription)
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        return view('doctor.addons.confirmation', compact('subscription'));
    }

    /**
     * Cancel an add-on subscription.
     */
    public function cancel(UserAddonSubscription $subscription)
    {
        $user = auth()->user();

        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        if ($subscription->status !== 'active') {
            return back()->with('error', 'Only active subscriptions can be cancelled.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Add-on subscription cancelled.');
    }
}
