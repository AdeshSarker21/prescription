@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Complete Payment</h1>

                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Plan</p>
                            <p class="text-lg font-bold text-gray-900">{{ $plan->name }}</p>
                        </div>
                        @php
                            $cycleLabel = match($cycle) {
                                'monthly' => 'Monthly',
                                'quarterly' => '3 Months',
                                'semi_annual' => '6 Months',
                                'yearly' => '12 Months',
                                'lifetime' => 'Lifetime',
                                default => ucfirst($cycle),
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $plan->slug === 'basic' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $plan->slug === 'pro' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $plan->slug === 'premium' ? 'bg-purple-100 text-purple-700' : '' }}">
                            {{ $cycleLabel }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ config('app.currency', '$') }}{{ number_format($price, 2) }}</p>
                    </div>
                    @if($cycle !== 'lifetime' && $plan->monthly_price > 0)
                    @php
                        $cycleMonths = match($cycle) { 'quarterly' => 3, 'semi_annual' => 6, 'yearly' => 12, default => 1 };
                        $monthlyCost = $plan->monthly_price * $cycleMonths;
                        $savings = $monthlyCost - $price;
                    @endphp
                    @if($savings > 0)
                    <p class="text-sm text-green-600 font-medium mt-2">You save {{ config('app.currency', '$') }}{{ number_format($savings, 2) }} vs monthly billing</p>
                    @endif
                    @endif
                </div>

                <form action="{{ route('subscription.payment.success') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                    <input type="hidden" name="price" value="{{ $price }}">

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800">
                        <strong>Demo mode:</strong> No real payment will be processed. Click "Pay Now" to simulate a successful payment.
                    </div>

                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                        Pay {{ config('app.currency', '$') }}{{ number_format($price, 2) }}
                    </button>

                    <a href="{{ route('subscription.plans') }}" class="block text-center mt-4 text-sm text-gray-500 hover:text-gray-700">
                        Cancel and go back
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection
