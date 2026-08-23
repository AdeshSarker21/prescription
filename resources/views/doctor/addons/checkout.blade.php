@extends('doctor.layouts.app')

@section('title', 'Add-on Checkout')

@section('header', 'Add-on Payment')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="dashboard-card animate-card overflow-hidden">
        <div style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;text-align:center;padding:24px;">
            <h2 class="text-xl font-bold">Complete Payment</h2>
            <p class="text-sm mt-1 opacity-80">{{ $subscription->moduleAddon->name }} - {{ $subscription->getBillingCycleLabel() }}</p>
        </div>

        <div style="padding:32px;">
            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Add-on</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->moduleAddon->name }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Billing Cycle</span>
                    <span class="font-semibold" style="color:var(--text-primary);">{{ $subscription->getBillingCycleLabel() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b" style="border-color:rgba(255,255,255,0.05);">
                    <span style="color:var(--text-muted);">Amount</span>
                    <span class="text-2xl font-extrabold" style="color:var(--text-primary);">{{ config('app.currency') }}{{ number_format($subscription->amount_paid, 2) }}</span>
                </div>
            </div>

            <div class="mb-6 p-4 rounded-xl" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
                <h4 class="text-sm font-semibold mb-2" style="color:var(--text-primary);">Payment Instructions</h4>
                <ol class="text-sm space-y-1" style="color:var(--text-muted);">
                    <li>1. Send payment via bKash to: <strong>01XXXXXXXXX</strong></li>
                    <li>2. Enter the transaction ID below</li>
                    <li>3. Admin will verify and activate your add-on</li>
                </ol>
            </div>

            <form action="{{ route('doctor.addons.process-payment', $subscription) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Transaction ID <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="transaction_id" required placeholder="e.g. 8A3B5C7D9E1F2G3H"
                        class="w-full px-4 py-3 rounded-xl text-sm"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:var(--text-primary);">
                    @error('transaction_id') <p class="mt-1 text-xs" style="color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Sender Number <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="sender_number" required placeholder="e.g. 01XXXXXXXXX"
                        class="w-full px-4 py-3 rounded-xl text-sm"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:var(--text-primary);">
                    @error('sender_number') <p class="mt-1 text-xs" style="color:#ef4444;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Payment Note (Optional)</label>
                    <textarea name="payment_note" rows="2" placeholder="Any notes about this payment..."
                        class="w-full px-4 py-3 rounded-xl text-sm"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:var(--text-primary);"></textarea>
                </div>

                <button type="submit" class="w-full py-3 text-sm font-semibold rounded-xl text-white transition-all" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                    Submit Payment
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('doctor.addons.index') }}" class="text-sm" style="color:var(--text-muted);">Cancel &amp; go back</a>
            </div>
        </div>
    </div>
</div>
@endsection
