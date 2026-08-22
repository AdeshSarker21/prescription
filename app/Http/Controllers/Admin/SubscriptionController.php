<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
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

    public function index(Request $request): View
    {
        $query = Subscription::with(['user', 'plan', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest()->paginate(20)->withQueryString();

        $baseQuery = Subscription::query();
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('billing_cycle')) {
            $baseQuery->where('billing_cycle', $request->billing_cycle);
        }
        if ($request->filled('plan_id')) {
            $baseQuery->where('plan_id', $request->plan_id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'expired' => (clone $baseQuery)->where('status', 'expired')->count(),
            'revenue' => (clone $baseQuery)->where('status', 'active')
                ->sum('amount_paid'),
        ];

        $plans = Plan::orderBy('sort_order')->get(['id', 'name']);

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'plans'));
    }

    public function edit(Subscription $subscription): View
    {
        $plans = Plan::all();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:active,expired,cancelled,pending',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annual,yearly,lifetime',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        // Auto-calculate end date if not provided
        if (empty($data['ends_at']) && !empty($data['starts_at'])) {
            $plan = Plan::find($data['plan_id']);
            if ($plan) {
                $start = Carbon::parse($data['starts_at']);
                $data['ends_at'] = $start->addDays($plan->getDurationDays($data['billing_cycle']));
            }
        }

        $subscription->update($data);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription deleted successfully.');
    }

    public function approve(Subscription $subscription): RedirectResponse
    {
        abort_if($subscription->status !== 'pending', 404);

        $this->expiryService->activate($subscription, auth()->id());

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for {$subscription->user->name} has been approved and activated.");
    }

    public function reject(Subscription $subscription): RedirectResponse
    {
        abort_if($subscription->status !== 'pending', 404);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for {$subscription->user->name} has been rejected.");
    }

    /**
     * Show subscription history for a user.
     */
    public function history(Request $request): View
    {
        $query = Subscription::with(['user', 'plan', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $subscriptions = $query->paginate(30)->withQueryString();

        return view('admin.subscriptions.history', compact('subscriptions'));
    }
}
