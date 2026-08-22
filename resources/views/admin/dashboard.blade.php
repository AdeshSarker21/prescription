@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 animate-fade-in">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Dashboard</h1>
            <p class="text-sm text-white/50 mt-1">Welcome back, {{ Auth::user()->name }}! Here's what's happening today.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="glass-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white/50">Total Patients</p>
                    <p class="stat-value text-white mt-1">{{ number_format($totalPatients) }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-blue-500/20 to-blue-600/20 border border-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="glass-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white/50">Total Doctors</p>
                    <p class="stat-value text-white mt-1">{{ number_format($totalDoctors) }}</p>
                    <p class="text-xs text-emerald-400 mt-1">+{{ $newDoctorsThisMonth }} new this month</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 border border-emerald-500/20">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
        </div>

        <div class="glass-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white/50">Today's Appointments</p>
                    <p class="stat-value text-white mt-1">{{ number_format($todayAppointments) }}</p>
                    @if($pendingAppointments > 0)
                    <p class="text-xs text-amber-400 mt-1">{{ $pendingAppointments }} pending</p>
                    @endif
                </div>
                <div class="stat-icon bg-gradient-to-br from-amber-500/20 to-amber-600/20 border border-amber-500/20">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="glass-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white/50">Monthly Revenue</p>
                    <p class="stat-value text-white mt-1">{{ config('app.currency') }}{{ number_format($totalRevenue, 0) }}</p>
                </div>
                <div class="stat-icon bg-gradient-to-br from-purple-500/20 to-purple-600/20 border border-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Items Row --}}
    @if($pendingApprovals > 0 || $pendingSubscriptions > 0 || $pendingSuggestions > 0)
    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6 animate-fade-in">
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-sm font-semibold text-amber-400">Pending Items:</span>
            @if($pendingApprovals > 0)
            <a href="{{ route('admin.approvals.index') }}" class="inline-flex items-center gap-1.5 text-sm text-amber-400 hover:text-amber-300 transition-colors">
                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                {{ $pendingApprovals }} doctor approval(s)
            </a>
            @endif
            @if($pendingSubscriptions > 0)
            <a href="{{ route('admin.subscriptions.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-1.5 text-sm text-amber-400 hover:text-amber-300 transition-colors">
                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                {{ $pendingSubscriptions }} subscription(s) pending
            </a>
            @endif
            @if($pendingSuggestions > 0)
            <span class="inline-flex items-center gap-1.5 text-sm text-amber-400">
                <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                {{ $pendingSuggestions }} medicine suggestion(s)
            </span>
            @endif
        </div>
    </div>
    @endif

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 glass-card-static animate-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white/90">Revenue Overview (Monthly)</h3>
                <span class="text-xs text-white/40">Current Year</span>
            </div>
            <div class="h-64 flex items-end justify-between gap-2 px-2">
                @foreach ($revenueMonths as $i => $month)
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full bg-gradient-to-t from-indigo-500/60 to-purple-500/40 rounded-t-md transition-all duration-500 hover:from-indigo-400/80 hover:to-purple-400/60 relative cursor-pointer" style="height: {{ $maxRevenue > 0 ? max(($monthlyRevenueData[$i] / $maxRevenue) * 100, 2) : 2 }}%">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 glass-dropdown text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-1 group-hover:translate-y-0">
                                {{ config('app.currency') }}{{ number_format($monthlyRevenueData[$i], 0) }}
                            </div>
                        </div>
                        <span class="text-xs text-white/40">{{ $month }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Overview --}}
        <div class="glass-card-static animate-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white/90">Quick Overview</h3>
            </div>
            <div class="space-y-3">
                <a href="{{ route('admin.subscriptions.index', ['status' => 'pending']) }}" class="flex items-center justify-between p-3 rounded-lg bg-amber-500/10 border border-amber-500/10 hover:bg-amber-500/15 transition-all duration-200 group">
                    <div>
                        <p class="text-sm font-medium text-white/80 group-hover:text-white transition-colors">Pending Subscriptions</p>
                        <p class="text-xs text-white/40">Awaiting approval</p>
                    </div>
                    <span class="text-lg font-bold text-amber-400">{{ $pendingSubscriptions }}</span>
                </a>
                <a href="{{ route('admin.approvals.index') }}" class="flex items-center justify-between p-3 rounded-lg bg-blue-500/10 border border-blue-500/10 hover:bg-blue-500/15 transition-all duration-200 group">
                    <div>
                        <p class="text-sm font-medium text-white/80 group-hover:text-white transition-colors">Doctor Approvals</p>
                        <p class="text-xs text-white/40">New registrations</p>
                    </div>
                    <span class="text-lg font-bold text-blue-400">{{ $pendingApprovals }}</span>
                </a>
                <div class="flex items-center justify-between p-3 rounded-lg bg-purple-500/10 border border-purple-500/10">
                    <div>
                        <p class="text-sm font-medium text-white/80">Medicine Suggestions</p>
                        <p class="text-xs text-white/40">Pending review</p>
                    </div>
                    <span class="text-lg font-bold text-purple-400">{{ $pendingSuggestions }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/10">
                    <div>
                        <p class="text-sm font-medium text-white/80">Today's Appointments</p>
                        <p class="text-xs text-white/40">Across all doctors</p>
                    </div>
                    <span class="text-lg font-bold text-emerald-400">{{ $todayAppointments }}</span>
                </div>
                <a href="{{ route('admin.assistants.index') }}" class="flex items-center justify-between p-3 rounded-lg bg-cyan-500/10 border border-cyan-500/10 hover:bg-cyan-500/15 transition-all duration-200 group">
                    <div>
                        <p class="text-sm font-medium text-white/80 group-hover:text-white transition-colors">Assistants</p>
                        <p class="text-xs text-white/40">Manage assignments</p>
                    </div>
                    <svg class="w-5 h-5 text-cyan-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Appointments Table --}}
    <div class="glass-table animate-card mb-6">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white/90">Recent Appointments</h3>
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Patient</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Doctor</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Date</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentAppointments as $apt)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500/30 to-purple-600/30 flex items-center justify-center text-xs font-semibold text-indigo-300 border border-indigo-500/20">
                                        {{ strtoupper(substr($apt->patient?->name ?? '??', 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-white/80">{{ $apt->patient?->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-white/60">{{ $apt->doctor?->name ?? 'Unknown' }}</td>
                            <td class="px-5 py-4 text-white/60">{{ $apt->appointment_date?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'scheduled' => 'bg-blue-500/20 text-blue-400',
                                        'confirmed' => 'bg-emerald-500/20 text-emerald-400',
                                        'completed' => 'bg-white/10 text-white/50',
                                        'cancelled' => 'bg-red-500/20 text-red-400',
                                    ];
                                @endphp
                                <span class="status-badge {{ $statusColors[$apt->status] ?? 'bg-white/10 text-white/50' }}">
                                    {{ ucfirst($apt->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-white/40">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
