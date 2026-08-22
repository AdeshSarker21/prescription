@extends('admin.layouts.app')

@section('title', 'Subscription History')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white/90">Subscription History</h1>
                <p class="mt-1 text-sm text-white/50">Complete history of all subscriptions, renewals, and payments.</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="btn-outline-glass">
                &larr; Back to Subscriptions
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Activated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Renewed From</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($subscriptions as $sub)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white/90">{{ $sub->user->name ?? 'Deleted User' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $sub->plan->slug === 'basic' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $sub->plan->slug === 'pro' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                        {{ $sub->plan->slug === 'premium' ? 'bg-purple-500/20 text-purple-400' : '' }}">
                                        {{ $sub->plan->name ?? 'Deleted' }}
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->activated_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->ends_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">
                                    @if($sub->renewedFrom)
                                        <span class="text-blue-400">#{{ $sub->renewed_from }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $sub->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-white/50">No subscription history found.</td>
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
