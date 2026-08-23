@extends('doctor.layouts.app')

@section('title', 'Add-ons')

@section('header', 'Module Add-ons')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-8 animate-card">
        <h2 class="text-3xl font-bold" style="color:var(--text-primary);">Module Add-ons</h2>
        <p class="mt-2" style="color:var(--text-muted);">Enhance your practice with optional add-on modules</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);color:#10b981;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;">
        {{ session('error') }}
    </div>
    @endif

    @if(session('info'))
    <div class="mb-6 p-4 rounded-xl" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);color:#6366f1;">
        {{ session('info') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="addonGrid">
        @forelse($addons as $addon)
        @php
            $isSubscribed = in_array($addon->id, $activeAddonIds);
        @endphp
        <div class="plan-card dashboard-card animate-card overflow-hidden" style="padding:0;display:flex;flex-direction:column;position:relative;">
            <div style="padding:32px 28px;flex:1;display:flex;flex-direction:column;">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold" style="color:var(--text-primary);">{{ $addon->name }}</h3>
                        @if($isSubscribed)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(16,185,129,0.15);color:#10b981;">Active</span>
                        @endif
                    </div>

                    @if($addon->module)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-2" style="background:rgba(99,102,241,0.15);color:#6366f1;">
                        {{ $addon->module->name }}
                    </span>
                    @endif

                    @if($addon->description)
                    <p class="text-sm mt-3" style="color:var(--text-muted);">{{ $addon->description }}</p>
                    @endif

                    <div class="mt-6">
                        @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                        <div class="cycle-price cycle-price-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }}">
                            <span class="text-4xl font-extrabold" style="color:var(--text-primary);">{{ config('app.currency') }}{{ number_format($addon->getPriceForCycle($cycle), 0) }}</span>
                            <span class="text-sm" style="color:var(--text-muted);">
                                @if($cycle === 'monthly')/month
                                @elseif($cycle === 'quarterly')/3 months
                                @elseif($cycle === 'semi_annual')/6 months
                                @elseif($cycle === 'yearly')/year
                                @elseif($cycle === 'lifetime')one-time
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8">
                    @if($isSubscribed)
                    <div class="w-full py-3 text-sm font-semibold rounded-xl text-center" style="background:rgba(16,185,129,0.15);color:#10b981;cursor:default;">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Subscribed
                    </div>
                    @else
                    @foreach(['monthly', 'quarterly', 'semi_annual', 'yearly', 'lifetime'] as $cycle)
                    <form action="{{ route('doctor.addons.purchase', $addon) }}" method="POST" class="cycle-form cycle-form-{{ $cycle }} {{ $cycle !== 'monthly' ? 'hidden' : '' }}">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="{{ $cycle }}">
                        <button type="submit" class="w-full btn-gradient" style="text-align:center;display:block;">
                            @if($addon->getPriceForCycle($cycle) <= 0) Get Free Access
                            @elseif($cycle === 'lifetime') Pay Once
                            @else Subscribe @endif
                        </button>
                    </form>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full dashboard-card p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4" style="color:var(--text-muted);opacity:0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <h3 class="text-lg font-semibold" style="color:var(--text-primary);">No Add-ons Available</h3>
            <p class="text-sm mt-2" style="color:var(--text-muted);">Check back later for new add-on modules.</p>
        </div>
        @endforelse
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
    if (tab) {
        tab.style.background = '#6366f1';
        tab.style.color = 'white';
        tab.style.boxShadow = '0 4px 14px rgba(99,102,241,0.25)';
    }

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
