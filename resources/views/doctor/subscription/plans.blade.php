@extends('doctor.layouts.app')

@section('title', 'Subscription Plans')

@section('header', 'Subscription Plans')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-8 animate-card">
        <h2 class="text-3xl font-bold" style="color:var(--text-primary);">Choose Your Plan</h2>
        <p class="mt-2" style="color:var(--text-muted);">Select the plan that best fits your practice needs</p>
    </div>

    <div class="flex items-center justify-center flex-wrap gap-2 mb-10 animate-card" id="billingToggle">
        <button type="button" onclick="setBilling('monthly')" id="monthlyTab" class="toggle-tab px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:#6366f1;color:white;box-shadow:0 4px 14px rgba(99,102,241,0.25);">Monthly</button>
        <button type="button" onclick="setBilling('quarterly')" id="quarterlyTab" class="toggle-tab px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:rgba(148,163,184,0.15);color:var(--text-muted);">3 Months</button>
        <button type="button" onclick="setBilling('semi_annual')" id="semi_annualTab" class="toggle-tab px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:rgba(148,163,184,0.15);color:var(--text-muted);">6 Months</button>
        <button type="button" onclick="setBilling('yearly')" id="yearlyTab" class="toggle-tab px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:rgba(148,163,184,0.15);color:var(--text-muted);">12 Months</button>
        <button type="button" onclick="setBilling('lifetime')" id="lifetimeTab" class="toggle-tab px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-200" style="background:rgba(148,163,184,0.15);color:var(--text-muted);">Lifetime</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8" id="planGrid">
        @foreach($plans as $plan)
        <div class="plan-card dashboard-card animate-card overflow-hidden {{ $currentPlanId == $plan->id ? 'ring-2 ring-indigo-500' : '' }}" style="padding:0;display:flex;flex-direction:column;position:relative;">
            @if($plan->is_popular)
            <div style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;text-align:center;padding:6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Most Popular</div>
            @endif
            <div style="padding:32px 28px;flex:1;display:flex;flex-direction:column;">
                <div>
                    <h3 class="text-xl font-bold" style="color:var(--text-primary);">{{ $plan->name }}</h3>
                    @if($plan->description)
                    <p class="text-sm mt-1" style="color:var(--text-muted);">{{ $plan->description }}</p>
                    @endif

                    <div class="mt-6">
                        @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                        <div class="cycle-price cycle-price-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }}">
                            <span class="text-4xl font-extrabold" style="color:var(--text-primary);">{{ config('app.currency') }}{{ number_format($plan->getPriceForCycle($cycle), 0) }}</span>
                            <span class="text-sm" style="color:var(--text-muted);">
                                @if($cycle === 'monthly')/month
                                @elseif($cycle === 'quarterly')/3 months
                                @elseif($cycle === 'semi_annual')/6 months
                                @elseif($cycle === 'yearly')/year
                                @elseif($cycle === 'lifetime')one-time
                                @endif
                            </span>
                            @if($cycle !== 'lifetime' && $cycle !== 'monthly' && $plan->monthly_price > 0)
                            @php
                                $monthlyCost = $plan->monthly_price;
                                $cycleMonths = match($cycle) { 'quarterly' => 3, 'semi_annual' => 6, 'yearly' => 12, default => 1 };
                                $savings = ($monthlyCost * $cycleMonths) - $plan->getPriceForCycle($cycle);
                            @endphp
                            @if($savings > 0)
                            <p class="text-xs mt-1 font-semibold" style="color:#10b981;">Save {{ config('app.currency') }}{{ number_format($savings, 0) }}</p>
                            @endif
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($plan->features && count($plan->features) > 0)
                <ul class="mt-8 space-y-4 flex-1">
                    @foreach($plan->features as $feature)
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm" style="color:var(--text-primary);">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif

                {{-- Included Modules --}}
                @if($plan->includedModules && count($plan->includedModules) > 0)
                <div class="mt-4 pt-4" style="border-top:1px solid rgba(255,255,255,0.05);">
                    <p class="text-xs font-semibold mb-2" style="color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Included Modules</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($plan->includedModules as $module)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                style="background:rgba(99,102,241,0.15);color:#6366f1;">
                                {{ $module->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-8">
                    @if($currentPlanId == $plan->id)
                    <div class="w-full py-3 text-sm font-semibold rounded-xl text-center" style="background:rgba(148,163,184,0.15);color:var(--text-muted);cursor:default;">Current Plan</div>
                    @else
                    @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                    <form action="{{ route('doctor.subscription.subscribe', $plan) }}" method="POST" class="cycle-form cycle-form-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }}">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                        <button type="submit" class="w-full btn-gradient" style="text-align:center;display:block;">
                            @if($plan->isFree()) Get Started
                            @elseif($cycle === 'lifetime') Pay Once
                            @else Subscribe @endif
                        </button>
                    </form>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center mt-10 animate-card">
        <a href="{{ route('doctor.subscription') }}" class="text-sm font-medium transition-all" style="color:#6366f1;padding:8px 20px;border-radius:10px;background:rgba(99,102,241,0.08);display:inline-block;">&larr; Back to Subscription</a>
    </div>
</div>

@push('scripts')
<script>
function setBilling(cycle) {
    document.querySelectorAll('.toggle-tab').forEach(t => {
        t.style.background = 'rgba(148,163,184,0.15)';
        t.style.color = '#64748b';
        t.style.boxShadow = 'none';
    });
    const tab = document.getElementById(cycle + 'Tab');
    tab.style.background = '#6366f1';
    tab.style.color = 'white';
    tab.style.boxShadow = '0 4px 14px rgba(99,102,241,0.25)';

    document.querySelectorAll('.plan-card').forEach(card => {
        // Hide all cycle prices and forms
        card.querySelectorAll('[class*="cycle-price"]').forEach(el => el.classList.add('hidden'));
        card.querySelectorAll('[class*="cycle-form"]').forEach(el => el.classList.add('hidden'));

        // Show selected cycle
        card.querySelectorAll('.cycle-price-' + cycle).forEach(el => el.classList.remove('hidden'));
        card.querySelectorAll('.cycle-form-' + cycle).forEach(el => el.classList.remove('hidden'));
    });
}
</script>
@endpush
@endsection
