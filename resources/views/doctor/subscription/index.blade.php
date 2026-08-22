@extends('doctor.layouts.app')

@section('title', 'Subscription')

@section('header', 'Subscription')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Expiry Warning Banner --}}
    @if($subscription && $subscription->status === 'active' && $subscription->isExpiringSoon())
    @php $days = $subscription->daysUntilExpiry(); @endphp
    <div class="animate-card rounded-xl p-4 flex items-start gap-3" style="background:rgba(245,158,11,0.1);backdrop-filter:blur(12px);border:1px solid rgba(245,158,11,0.2);">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold" style="color:#92400e;">Subscription Expiring Soon</p>
            <p class="text-sm mt-1" style="color:#a16207;">Your subscription expires in {{ $days }} day(s). Please renew to avoid service interruption.</p>
        </div>
        <a href="{{ route('doctor.subscription.plans') }}" class="px-4 py-2 text-sm font-semibold rounded-lg" style="background:rgba(245,158,11,0.2);color:#92400e;">Renew Now</a>
    </div>
    @endif

    {{-- Pending Approval Banner --}}
    @if($subscription && $subscription->status === 'pending')
    <div class="animate-card rounded-xl p-4 flex items-start gap-3" style="background:rgba(245,158,11,0.1);backdrop-filter:blur(12px);border:1px solid rgba(245,158,11,0.2);">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div>
            <p class="font-semibold" style="color:#92400e;">Subscription Pending Approval</p>
            <p class="text-sm mt-1" style="color:#a16207;">Your payment has been submitted and is awaiting admin approval. You will be able to access all features once approved.</p>
        </div>
    </div>
    @endif

    @if($subscription)
    {{-- Current Plan Card --}}
    <div class="dashboard-card animate-card overflow-hidden" style="padding:0;">
        <div style="background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(79,70,229,0.15));backdrop-filter:blur(12px);padding:28px 32px;border-bottom:1px solid rgba(255,255,255,0.4);">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider" style="color:var(--text-muted);">Current Plan</p>
                    <h2 class="text-2xl font-bold mt-1" style="color:var(--text-primary);">{{ $subscription->plan->name ?? 'No Active Plan' }}</h2>
                    @if($subscription->plan)
                    <p class="mt-1" style="color:var(--text-muted);">
                        @php $amount = $subscription->amount_paid ?? $subscription->plan->getPriceForCycle($subscription->billing_cycle); @endphp
                        {{ config('app.currency') }}{{ number_format($amount, 0) }}/{{ $subscription->getBillingCycleLabel() }}
                    </p>
                    @endif
                </div>
                <div>
                    @if($subscription->status == 'active')
                        @if($subscription->isExpiringSoon())
                        <span class="status-badge" style="background:rgba(245,158,11,0.12);color:#d97706;">Expiring Soon</span>
                        @else
                        <span class="status-badge" style="background:rgba(16,185,129,0.12);color:#059669;">Active</span>
                        @endif
                    @elseif($subscription->status == 'pending')
                    <span class="status-badge" style="background:rgba(245,158,11,0.12);color:#d97706;">Pending Approval</span>
                    @elseif($subscription->status == 'canceled' || $subscription->status == 'cancelled')
                    <span class="status-badge" style="background:rgba(239,68,68,0.12);color:#dc2626;">Canceled</span>
                    @elseif($subscription->status == 'expired')
                    <span class="status-badge" style="background:rgba(100,116,139,0.12);color:#475569;">Expired</span>
                    @else
                    <span class="status-badge" style="background:rgba(245,158,11,0.12);color:#d97706;">{{ ucfirst($subscription->status) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="padding:24px 32px;">
            @if($subscription->plan && $subscription->plan->features)
            <div>
                <p class="text-xs font-medium uppercase tracking-wider mb-3" style="color:var(--text-muted);">Features</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($subscription->plan->features as $feature)
                    <div class="flex items-center gap-3 text-sm" style="color:var(--text-primary);">
                        <svg class="w-4 h-4 flex-shrink-0" style="color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $feature }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="flex flex-wrap items-center gap-6 pt-5 mt-5" style="border-top:1px solid rgba(148,163,184,0.2);">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Billing Cycle</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--text-primary);">{{ $subscription->getBillingCycleLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Start Date</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--text-primary);">{{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">End Date</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--text-primary);">{{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</p>
                </div>
                @if($subscription->daysUntilExpiry() !== null)
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Days Remaining</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:{{ $subscription->daysUntilExpiry() <= 3 ? '#dc2626' : ($subscription->daysUntilExpiry() <= 7 ? '#d97706' : '#059669') }};">{{ $subscription->daysUntilExpiry() }} day(s)</p>
                </div>
                @endif
            </div>
            <div class="flex items-center gap-3 pt-5">
                <a href="{{ route('doctor.subscription.plans') }}" class="btn-gradient">Upgrade Plan</a>
                @if($subscription->status == 'active')
                <form action="{{ route('doctor.subscription.cancel') }}" method="POST" data-confirm="Are you sure you want to cancel your subscription?" onsubmit="return confirm(this.getAttribute('data-confirm'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding:10px 20px;border-radius:10px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.2);font-weight:600;font-size:13px;cursor:pointer;transition:all 0.25s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'">Cancel Subscription</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Billing Info --}}
    <div class="dashboard-card animate-card">
        <h3 class="text-lg font-semibold mb-4" style="color:var(--text-primary);">Billing Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="rounded-lg p-4" style="background:rgba(255,255,255,0.3);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.3);">
                <p class="text-xs font-medium uppercase tracking-wider" style="color:var(--text-muted);">Billing Email</p>
                <p class="text-sm font-semibold mt-1" style="color:var(--text-primary);">{{ auth()->user()->email }}</p>
            </div>
            <div class="rounded-lg p-4" style="background:rgba(255,255,255,0.3);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.3);">
                <p class="text-xs font-medium uppercase tracking-wider" style="color:var(--text-muted);">Payment Method</p>
                <p class="text-sm font-semibold mt-1" style="color:var(--text-primary);">{{ $subscription->payment_method ? strtoupper($subscription->payment_method) : 'N/A' }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="dashboard-card animate-card text-center" style="padding:48px 24px;">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background:rgba(148,163,184,0.15);">
            <svg class="w-8 h-8" style="color:#94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold" style="color:var(--text-primary);">No Active Subscription</h3>
        <p class="text-sm mt-2" style="color:var(--text-muted);">You haven't subscribed to any plan yet.</p>
        <a href="{{ route('doctor.subscription.plans') }}" class="btn-gradient inline-flex items-center gap-2 mt-5">View Plans</a>
    </div>
    @endif

    {{-- Payment History --}}
    <div class="dashboard-card animate-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold" style="color:var(--text-primary);">Payment History</h3>
            <a href="{{ route('doctor.subscription.payment-history') }}" class="text-sm font-medium transition-all" style="color:#6366f1;padding:6px 14px;border-radius:8px;background:rgba(99,102,241,0.08);">View All &rarr;</a>
        </div>
        @if($paymentHistory && $paymentHistory->count() > 0)
        <div class="glass-table">
            <table>
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Billing</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentHistory as $payment)
                    <tr>
                        <td><span class="font-medium">{{ $payment->plan_name }}</span></td>
                        <td style="color:var(--text-muted);">{{ $payment->getBillingCycleLabel() }}</td>
                        <td style="color:var(--text-primary);">{{ config('app.currency', '$') }}{{ number_format($payment->amount, 2) }}</td>
                        <td style="color:var(--text-muted);">{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                        <td>
                            <span class="status-badge
                                @if($payment->status === 'active')" style="background:rgba(16,185,129,0.1);color:#059669;"
                                @elseif($payment->status === 'pending')" style="background:rgba(245,158,11,0.1);color:#d97706;"
                                @elseif($payment->status === 'cancelled')" style="background:rgba(239,68,68,0.1);color:#dc2626;"
                                @elseif($payment->status === 'expired')" style="background:rgba(100,116,139,0.1);color:#475569;"
                                @else" style="background:rgba(100,116,139,0.1);color:#475569;"
                                @endif>
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-sm" style="color:var(--text-muted);">No payment history available.</p>
        </div>
        @endif
    </div>
</div>
@endsection
