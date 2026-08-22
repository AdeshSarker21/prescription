@extends('doctor.layouts.app')

@section('title', 'Payment Submitted')

@section('header', 'Payment Submitted')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-green-700 px-6 py-8 text-white text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold">Payment Submitted!</h2>
            <p class="text-green-200 mt-2">Your payment information has been received.</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <h3 class="font-semibold text-gray-900">Payment Summary</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-500">Plan</p>
                        <p class="font-medium text-gray-900">{{ $subscription->plan->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Amount</p>
                        @php $amount = $subscription->billing_cycle === 'yearly' ? $subscription->plan->yearly_price : $subscription->plan->monthly_price; @endphp
                        <p class="font-medium text-gray-900">{{ config('app.currency') }}{{ number_format($amount, 0) }}/{{ $subscription->billing_cycle }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Transaction ID</p>
                        <p class="font-medium text-gray-900">{{ $subscription->transaction_id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Sender Number</p>
                        <p class="font-medium text-gray-900">{{ $subscription->sender_number }}</p>
                    </div>
                </div>
            </div>

            <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-yellow-800">Pending Approval</p>
                        <p class="text-sm text-yellow-700 mt-1">Your subscription is awaiting admin approval. You will be notified once it is approved. This usually takes a few hours.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('doctor.subscription') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">View Subscription</a>
            </div>
        </div>
    </div>
</div>
@endsection
