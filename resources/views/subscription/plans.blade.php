@extends('layouts.app')

@section('title', 'Choose a Plan')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
                <p class="mt-2 text-lg text-gray-600">Select the plan that best fits your practice needs.</p>
                @if ($currentPlan)
                    <div class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full text-sm">
                        Current plan: <strong class="ml-1">{{ $currentPlan->name }}</strong>
                    </div>
                @endif
            </div>

            {{-- Billing Cycle Toggle --}}
            <div class="flex items-center justify-center flex-wrap gap-2 mb-10" id="billingToggle">
                <button type="button" onclick="setBilling('monthly')" id="monthlyTab" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#4f46e5;color:white;">Monthly</button>
                <button type="button" onclick="setBilling('quarterly')" id="quarterlyTab" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#e5e7eb;color:#6b7280;">3 Months</button>
                <button type="button" onclick="setBilling('semi_annual')" id="semi_annualTab" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#e5e7eb;color:#6b7280;">6 Months</button>
                <button type="button" onclick="setBilling('yearly')" id="yearlyTab" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#e5e7eb;color:#6b7280;">12 Months</button>
                <button type="button" onclick="setBilling('lifetime')" id="lifetimeTab" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#e5e7eb;color:#6b7280;">Lifetime</button>
            </div>

            @if (session('error'))
                <div class="max-w-xl mx-auto mb-6 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach ($plans as $plan)
                    <div class="plan-card bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col {{ $currentPlan && $currentPlan->id === $plan->id ? 'ring-2 ring-indigo-500' : '' }}">
                        {{-- Header --}}
                        <div class="px-6 py-8 text-center border-b border-gray-100
                            {{ $plan->slug === 'basic' ? 'bg-green-50' : '' }}
                            {{ $plan->slug === 'pro' ? 'bg-blue-50' : '' }}
                            {{ $plan->slug === 'premium' ? 'bg-purple-50' : '' }}">
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>

                            @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                            @php
                                $cycleLabel = match($cycle) {
                                    'monthly' => '/mo',
                                    'quarterly' => '/3 mo',
                                    'semi_annual' => '/6 mo',
                                    'yearly' => '/yr',
                                    'lifetime' => ' one-time',
                                    default => '',
                                };
                            @endphp
                            <div class="cycle-price cycle-price-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }} mt-4">
                                @if($plan->getPriceForCycle($cycle) > 0)
                                <span class="text-4xl font-extrabold text-gray-900">
                                    {{ config('app.currency', '$') }}{{ number_format($plan->getPriceForCycle($cycle), 2) }}
                                </span>
                                <span class="text-gray-500 text-sm">{{ $cycleLabel }}</span>
                                @else
                                <span class="text-4xl font-extrabold text-green-600">Free</span>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        {{-- Features --}}
                        <div class="px-6 py-6 flex-1">
                            <ul class="space-y-3">
                                @foreach ($plan->features ?? [] as $feature)
                                    <li class="flex items-start gap-2 text-sm text-gray-700">
                                        <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- CTA --}}
                        <div class="px-6 py-6 border-t border-gray-100">
                            @if ($currentPlan && $currentPlan->id === $plan->id)
                                <div class="text-center text-sm text-indigo-600 font-medium">Your Current Plan</div>
                            @else
                                @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                                <form action="{{ route('subscription.subscribe', $plan) }}" method="POST" class="cycle-form cycle-form-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }}">
                                    @csrf
                                    <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white
                                        {{ $plan->slug === 'basic' ? 'bg-gray-800 hover:bg-gray-900' : '' }}
                                        {{ $plan->slug === 'pro' ? 'bg-blue-600 hover:bg-blue-700' : '' }}
                                        {{ $plan->slug === 'premium' ? 'bg-purple-600 hover:bg-purple-700' : '' }}">
                                        {{ $plan->isFree() ? 'Get Started' : ($cycle === 'lifetime' ? 'Pay Once' : 'Subscribe') }}
                                    </button>
                                </form>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@push('scripts')
<script>
function setBilling(cycle) {
    document.querySelectorAll('#billingToggle button').forEach(t => {
        t.style.background = '#e5e7eb';
        t.style.color = '#6b7280';
    });
    const tab = document.getElementById(cycle + 'Tab');
    tab.style.background = '#4f46e5';
    tab.style.color = 'white';

    document.querySelectorAll('.plan-card').forEach(card => {
        card.querySelectorAll('[class*="cycle-price"]').forEach(el => el.classList.add('hidden'));
        card.querySelectorAll('[class*="cycle-form"]').forEach(el => el.classList.add('hidden'));

        card.querySelectorAll('.cycle-price-' + cycle).forEach(el => el.classList.remove('hidden'));
        card.querySelectorAll('.cycle-form-' + cycle).forEach(el => el.classList.remove('hidden'));
    });
}
</script>
@endpush
@endsection
