@extends('doctor.layouts.app')

@section('title', 'bKash Payment')

@section('header', 'Complete Payment')

@push('styles')
<style>
.send-payment-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    overflow: hidden;
    transition: transform 0.2s;
}

.send-payment-btn .btn-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #ec4899, #db2777);
    border-radius: inherit;
    transition: transform 0.3s;
}

.send-payment-btn::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 0.55rem;
    background: linear-gradient(135deg, #f472b6, #ec4899, #be185d);
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s;
}

.send-payment-btn:hover {
    transform: translateY(-2px);
}

.send-payment-btn:hover .btn-bg {
    transform: scale(1.05);
}

.send-payment-btn:hover::before {
    opacity: 1;
    animation: spin-glow 1.5s linear infinite;
}

.send-payment-btn:active {
    transform: translateY(0) scale(0.97);
}

@keyframes spin-glow {
    0% { filter: hue-rotate(0deg); }
    100% { filter: hue-rotate(360deg); }
}

.send-payment-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%);
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.send-payment-btn:hover::after {
    opacity: 1;
    animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.send-payment-btn:not(:hover) {
    animation: pulse-ring 2s ease-in-out infinite;
}

@keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 rgba(236, 72, 153, 0.4); }
    70% { box-shadow: 0 0 0 12px rgba(236, 72, 153, 0); }
    100% { box-shadow: 0 0 0 0 rgba(236, 72, 153, 0); }
}
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-pink-500 to-pink-700 px-6 py-6 text-white">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-2">
                    <svg class="w-8 h-8" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 18h16v12H16z" fill="currentColor" opacity="0.3"/>
                        <path d="M20 22h8v4h-8z" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">bKash Payment</h2>
                    <p class="text-pink-200 text-sm">Pay securely via bKash</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <h3 class="font-semibold text-gray-900">Plan: {{ $subscription->plan->name }}</h3>
                <p class="text-2xl font-bold text-gray-900">{{ config('app.currency') }}{{ number_format($amount, 0) }}/{{ $subscription->getBillingCycleLabel() }}</p>
                <p class="text-sm text-gray-500">{{ $subscription->getBillingCycleLabel() }} billing cycle</p>
            </div>

            <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                <h4 class="font-semibold text-yellow-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Send Payment
                </h4>
                <p class="text-sm text-yellow-700 mt-2">Send the exact amount to one of the numbers below, then fill in your transaction details.</p>
                @forelse($paymentMethods as $method)
                <div class="mt-3 bg-white rounded border border-yellow-300 p-3 text-center">
                    <p class="text-xs text-gray-500">{{ $method->name }} {{ $method->account_holder ? '— '.$method->account_holder : '' }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ $method->account_number }}</p>
                    @if($method->instructions)
                    <p class="text-xs text-gray-500 mt-1">{{ $method->instructions }}</p>
                    @endif
                </div>
                @empty
                <div class="mt-3 bg-white rounded border border-yellow-300 p-3 text-center">
                    <p class="text-sm text-gray-500">No payment methods configured. Please contact support.</p>
                </div>
                @endforelse
            </div>

            <form action="{{ route('doctor.subscription.process-payment', $subscription) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="transaction_id" class="block text-sm font-medium text-gray-700">bKash Transaction ID <span class="text-red-500">*</span></label>
                    <input type="text" name="transaction_id" id="transaction_id" value="{{ old('transaction_id') }}" placeholder="e.g. TRX123ABC" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('transaction_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sender_number" class="block text-sm font-medium text-gray-700">Your bKash Number <span class="text-red-500">*</span></label>
                    <input type="text" name="sender_number" id="sender_number" value="{{ old('sender_number') }}" placeholder="e.g. 01XXXXXXXXX" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('sender_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="payment_note" class="block text-sm font-medium text-gray-700">Note (Optional)</label>
                    <textarea name="payment_note" id="payment_note" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('payment_note') }}</textarea>
                    @error('payment_note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="send-payment-btn group">
                        <span class="btn-bg"></span>
                        <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="relative z-10">Send Payment</span>
                    </button>
                    <a href="{{ route('doctor.subscription.plans') }}" class="inline-flex items-center px-4 py-3 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
