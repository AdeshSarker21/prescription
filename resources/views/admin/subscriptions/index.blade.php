@php
    $statsLabels = ['Total', 'Active', 'Pending', 'Expired', 'Revenue'];
    $statsValues = [$stats['total'], $stats['active'], $stats['pending'], $stats['expired'] ?? 0, number_format($stats['revenue'], 2)];
    $statsIcons = ['currency-dollar', 'check-circle', 'clock', 'x-circle', 'trending-up'];
    $statsColors = ['blue', 'green', 'yellow', 'red', 'indigo'];
@endphp

@extends('admin.layouts.app')

@section('title', 'Subscriptions')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-white/90">Subscriptions</h1>
            <p class="mt-1 text-sm text-white/50">Manage all doctor subscriptions and history.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            @foreach ($statsLabels as $i => $label)
                <div class="glass-card-static p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-white/50">{{ $label }}</p>
                            <p class="text-2xl font-bold text-white/90 mt-1">{{ $statsValues[$i] }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-lg bg-{{ $statsColors[$i] }}-500/20 flex items-center justify-center">
                            <svg class="h-6 w-6 text-{{ $statsColors[$i] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($statsIcons[$i] === 'currency-dollar')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif ($statsIcons[$i] === 'check-circle')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif ($statsIcons[$i] === 'x-circle')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif ($statsIcons[$i] === 'clock')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                @endif
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="glass-card-static p-4 mb-6">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Doctor name or email..." class="w-48 glass-input">
                </div>
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Status</label>
                    <select name="status" class="px-3 py-2 glass-input">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Billing</label>
                    <select name="billing_cycle" class="px-3 py-2 glass-input">
                        <option value="">All</option>
                        <option value="monthly" {{ request('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('billing_cycle') === 'quarterly' ? 'selected' : '' }}>3 Months</option>
                        <option value="semi_annual" {{ request('billing_cycle') === 'semi_annual' ? 'selected' : '' }}>6 Months</option>
                        <option value="yearly" {{ request('billing_cycle') === 'yearly' ? 'selected' : '' }}>12 Months</option>
                        <option value="lifetime" {{ request('billing_cycle') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Plan</label>
                    <select name="plan_id" class="px-3 py-2 glass-input">
                        <option value="">All Plans</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 btn-gradient">Filter</button>
                    <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-white/10 text-white/60 text-sm font-medium rounded-lg hover:bg-white/20 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        {{-- Subscriptions Table --}}
        <div class="glass-card-static overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Billing</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">End Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-white/50 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($subscriptions as $sub)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white/90">{{ $sub->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $sub->plan->slug === 'basic' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $sub->plan->slug === 'pro' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                        {{ $sub->plan->slug === 'premium' ? 'bg-purple-500/20 text-purple-400' : '' }}">
                                        {{ $sub->plan->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="status-badge
                                        {{ $sub->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $sub->status === 'expired' ? 'bg-red-500/20 text-red-400' : '' }}
                                        {{ $sub->status === 'cancelled' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ $sub->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : '' }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->getBillingCycleLabel() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/70">{{ config('app.currency', '$') }}{{ number_format($sub->amount_paid ?? 0, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->starts_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->ends_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    @if($sub->status === 'pending' && $sub->payment_method)
                                        <form action="{{ route('admin.subscriptions.approve', $sub) }}" method="POST" class="inline" data-confirm="Approve this subscription?" onsubmit="return confirm(this.getAttribute('data-confirm'))">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-lg border border-emerald-500/20 hover:bg-emerald-500/20 hover:scale-105 active:scale-95 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.subscriptions.reject', $sub) }}" method="POST" class="inline" data-confirm="Reject this subscription?" onsubmit="return confirm(this.getAttribute('data-confirm'))">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500/10 text-red-400 text-xs font-medium rounded-lg border border-red-500/20 hover:bg-red-500/20 hover:scale-105 active:scale-95 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.subscriptions.edit', $sub) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/10 text-indigo-400 text-xs font-medium rounded-lg border border-indigo-500/20 hover:bg-indigo-500/20 hover:scale-105 active:scale-95 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-white/50">No subscriptions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-sm text-white/50">{{ $subscriptions->total() }} result(s)</p>
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
@endsection
