@extends('admin.layouts.app')

@section('title', 'Add-ons')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white/90">Module Add-ons</h1>
            <p class="mt-1 text-sm text-white/50">Manage optional add-ons that doctors can purchase separately.</p>
        </div>
        <a href="{{ route('admin.addons.create') }}" class="btn-gradient">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Add-on
        </a>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))
    <div class="mb-4 alert-glass">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($addons as $addon)
        <div class="glass-card-static overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white/90">{{ $addon->name }}</h3>
                    <span class="status-badge {{ $addon->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                        {{ $addon->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if($addon->module)
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        {{ $addon->module->name }}
                    </span>
                </div>
                @endif

                @if($addon->description)
                <p class="text-sm text-white/50 mt-2">{{ $addon->description }}</p>
                @endif

                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-lg p-2" style="background:rgba(255,255,255,0.05);">
                        <span class="text-white/40 text-xs">Monthly</span>
                        <p class="text-white/90 font-semibold">{{ config('app.currency', '$') }}{{ number_format($addon->monthly_price, 0) }}</p>
                    </div>
                    <div class="rounded-lg p-2" style="background:rgba(255,255,255,0.05);">
                        <span class="text-white/40 text-xs">3 Months</span>
                        <p class="text-white/90 font-semibold">{{ config('app.currency', '$') }}{{ number_format($addon->quarterly_price, 0) }}</p>
                    </div>
                    <div class="rounded-lg p-2" style="background:rgba(255,255,255,0.05);">
                        <span class="text-white/40 text-xs">6 Months</span>
                        <p class="text-white/90 font-semibold">{{ config('app.currency', '$') }}{{ number_format($addon->semi_annual_price, 0) }}</p>
                    </div>
                    <div class="rounded-lg p-2" style="background:rgba(255,255,255,0.05);">
                        <span class="text-white/40 text-xs">12 Months</span>
                        <p class="text-white/90 font-semibold">{{ config('app.currency', '$') }}{{ number_format($addon->yearly_price, 0) }}</p>
                    </div>
                    <div class="col-span-2 rounded-lg p-2" style="background:rgba(99,102,241,0.1);">
                        <span class="text-white/40 text-xs">Lifetime</span>
                        <p class="text-white/90 font-semibold">{{ config('app.currency', '$') }}{{ number_format($addon->lifetime_price, 0) }}</p>
                    </div>
                </div>

                <div class="mt-2 text-sm text-white/50">
                    <span>Active Subscriptions: {{ $addon->activeSubscriptions()->count() }}</span>
                    <span class="ml-3">Order: {{ $addon->sort_order }}</span>
                </div>

                <div class="mt-5 flex items-center gap-2">
                    <a href="{{ route('admin.addons.edit', $addon) }}" class="flex-1 text-center px-3 py-2 btn-gradient">Edit</a>
                    <form action="{{ route('admin.addons.destroy', $addon) }}" method="POST" data-confirm="Delete this add-on?" onsubmit="return confirm(this.getAttribute('data-confirm'))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg hover:bg-red-500/20 transition-all duration-200">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card-static p-12 text-center">
            <svg class="w-12 h-12 text-white/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <h3 class="text-lg font-semibold text-white/90">No Add-ons Yet</h3>
            <p class="text-sm text-white/50 mt-2">Create your first module add-on.</p>
            <a href="{{ route('admin.addons.create') }}" class="btn-gradient mt-4">Create Add-on</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
