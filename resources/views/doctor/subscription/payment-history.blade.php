@extends('doctor.layouts.app')

@section('title', 'Payment History')

@section('header', 'Payment History')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="animate-card">
        <a href="{{ route('doctor.subscription') }}" class="inline-flex items-center gap-2 text-sm font-medium transition-all" style="color:var(--text-muted);padding:8px 16px;border-radius:10px;background:rgba(255,255,255,0.4);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.3);">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Subscription
        </a>
    </div>

    <div class="dashboard-card animate-card overflow-hidden" style="padding:0;">
        <div style="padding:20px 24px;border-bottom:1px solid rgba(148,163,184,0.1);">
            <h3 class="text-lg font-semibold" style="color:var(--text-primary);">Payment History</h3>
            <p class="text-sm mt-1" style="color:var(--text-muted);">Complete record of all your subscriptions and payments.</p>
        </div>
        <div class="glass-table" style="border:none;border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Billing Cycle</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td><span class="font-medium">{{ $payment->plan_name ?? 'N/A' }}</span></td>
                        <td style="color:var(--text-primary);">{{ config('app.currency', '$') }}{{ number_format($payment->amount, 2) }}</td>
                        <td style="color:var(--text-muted);">{{ $payment->getBillingCycleLabel() }}</td>
                        <td style="color:var(--text-muted);">{{ $payment->starts_at ? $payment->starts_at->format('M d, Y') : 'N/A' }}</td>
                        <td style="color:var(--text-muted);">{{ $payment->ends_at ? $payment->ends_at->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @if(in_array($payment->status, ['active', 'paid', 'completed']))
                            <span class="status-badge" style="background:rgba(16,185,129,0.1);color:#059669;">{{ ucfirst($payment->status) }}</span>
                            @elseif($payment->status == 'pending')
                            <span class="status-badge" style="background:rgba(245,158,11,0.1);color:#d97706;">Pending</span>
                            @elseif(in_array($payment->status, ['expired', 'cancelled', 'failed']))
                            <span class="status-badge" style="background:rgba(239,68,68,0.1);color:#dc2626;">{{ ucfirst($payment->status) }}</span>
                            @else
                            <span class="status-badge" style="background:rgba(100,116,139,0.1);color:#475569;">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:var(--text-muted);font-size:14px;">No payment history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div style="padding:16px 24px;border-top:1px solid rgba(148,163,184,0.1);">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
