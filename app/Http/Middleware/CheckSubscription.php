<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionExpiryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $expiryService = app(SubscriptionExpiryService::class);
        $subscription = $user->subscription;

        // No subscription at all
        if (!$subscription) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'subscription_required',
                    'message' => 'Please subscribe to a plan to access this feature.',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('error', 'Please subscribe to a plan to access this feature.');
        }

        // Subscription is pending approval
        if ($subscription->status === 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'subscription_pending',
                    'message' => 'Your subscription is awaiting admin approval.',
                ], 403);
            }

            return redirect()->route('doctor.subscription')
                ->with('error', 'Your subscription is awaiting admin approval. Please wait for confirmation.');
        }

        // Subscription has expired
        if ($subscription->isExpired()) {
            // Auto-update status to expired
            if ($subscription->status === 'active') {
                $subscription->update(['status' => 'expired']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'subscription_expired',
                    'message' => 'Your subscription has expired. Please renew your package to continue using the system.',
                ], 403);
            }

            return redirect()->route('doctor.subscription.plans')
                ->with('error', 'Your subscription has expired. Please renew your package to continue using the system.');
        }

        // Check specific feature access
        if ($feature) {
            $plan = $subscription->plan;

            if (!$plan) {
                return redirect()->route('subscription.plans')
                    ->with('error', 'Your plan is no longer available. Please contact support.');
            }

            if (!$expiryService->canAccessFeature($user, $feature)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'feature_not_available',
                        'message' => 'Your current plan does not support this feature. Please upgrade.',
                    ], 403);
                }

                return redirect()->route('subscription.plans')
                    ->with('error', 'Your current plan does not support this feature. Please upgrade.');
            }

            if ($feature === 'max_patients' && !$plan->hasUnlimitedPatients()) {
                $patientCount = $user->patients()->count();
                if ($patientCount >= $plan->max_patients) {
                    return redirect()->route('subscription.plans')
                        ->with('error', 'You have reached the patient limit for your plan. Please upgrade.');
                }
            }
        }

        return $next($request);
    }
}
