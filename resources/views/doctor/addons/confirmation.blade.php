@extends('doctor.layouts.app')

@section('title', 'Payment Confirmation')

@section('header', 'Add-on Payment Confirmation')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="dashboard-card animate-card overflow-hidden">
        <div style="background:linear-gradient(135deg,#10b981,#059669);color:white;text-align:center;padding:24px;">
            <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold">Payment Submitted!</h2>
            <p class="text-sm mt-1 opacity-80">Your payment is being verified</p>
        </div>

        <div style="padding:32px;">
            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Add-on</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->moduleAddon->name }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Amount</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ config('app.currency') }}{{ number_format($subscription->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Billing Cycle</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->getBillingCycleLabel() }}</span>
                </div>
                @if($subscription->transaction_id)
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Transaction ID</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->transaction_id }}</span>
                </div>
                @endif
                @if($subscription->sender_number)
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Sender Number</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->sender_number }}</span>
                </div>
                @endif
            </div>

            <div class="p-4 rounded-xl text-center" style="background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.2);">
                <svg class="w-8 h-8 mx-auto mb-2" style="color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium" style="color:#fbbf24;">Pending Admin Approval</p>
                <p class="text-xs mt-1" style="color:var(--text-muted);">An admin will verify your payment and activate your add-on.</p>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('doctor.addons.index') }}" class="text-sm font-medium" style="color:#6366f1;">View My Add-ons</a>
            </div>
        </div>
    </div>
</div>
@endsection
