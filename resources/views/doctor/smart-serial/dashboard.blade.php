@extends('doctor.layouts.app')

@section('title', 'Smart Serial Dashboard')
@section('header', 'Smart Serial Dashboard')

@section('content')
<div class="space-y-6" x-data="serialDashboard()" x-init="init()">

    {{-- Chamber Tabs --}}
    @if($chambers->count() > 0)
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-sm font-semibold" style="color:var(--text-muted);">Chambers:</span>
        @foreach($chambers as $chamber)
            <a href="{{ route('doctor.smart-serial.dashboard', ['chamber_id' => $chamber->id]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($activeChamberId == $chamber->id || (!$activeChamberId && $loop->first)) ? 'bg-blue-600 text-white shadow-md' : 'bg-white/50 text-gray-600 hover:bg-white/70' }}">
                {{ $chamber->serial_prefix ? $chamber->serial_prefix . ' - ' : '' }}{{ $chamber->name }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- Session Info Bar --}}
    @if($session)
    <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Session Active</p>
                    <p class="text-lg font-bold" style="color:var(--text-primary);">Started {{ $session->started_at->format('h:i A') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($session->session_label)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(99,102,241,0.1);color:#6366f1;">{{ $session->session_label }}</span>
                @endif
                <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(16,185,129,0.1);color:#059669;">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block mr-1"></span>
                    {{ ucfirst($session->status) }}
                </span>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="voiceEnabled" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-medium" style="color:var(--text-muted);">Voice</span>
                </label>
                <a href="{{ route('doctor.smart-serial.index') }}" class="text-sm font-medium transition-all" style="color:#6366f1;padding:6px 14px;border-radius:8px;background:rgba(99,102,241,0.08);">Open Queue &rarr;</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Primary Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Today's Total --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Today's Total</p>
                    <p class="stat-value" style="color:#6366f1;margin-top:4px;" x-text="stats.total">{{ $stats['total'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Waiting --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #f59e0b;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Waiting</p>
                    <p class="stat-value" style="color:#d97706;margin-top:4px;" x-text="stats.waiting">{{ $stats['waiting'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));color:#f59e0b;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        {{-- Called --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #f97316;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Called</p>
                    <p class="stat-value" style="color:#ea580c;margin-top:4px;" x-text="stats.called">{{ $stats['called'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(249,115,22,0.12),rgba(249,115,22,0.06));color:#f97316;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                </div>
            </div>
        </div>

        {{-- Inside --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #3b82f6;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Inside</p>
                    <p class="stat-value" style="color:#2563eb;margin-top:4px;" x-text="stats.in_consultation">{{ $stats['in_consultation'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(59,130,246,0.06));color:#3b82f6;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
        </div>

        {{-- Completed --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #10b981;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Completed</p>
                    <p class="stat-value" style="color:#059669;margin-top:4px;" x-text="stats.completed">{{ $stats['completed'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(16,185,129,0.06));color:#10b981;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- Skipped --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #6b7280;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Skipped</p>
                    <p class="stat-value" style="color:#4b5563;margin-top:4px;" x-text="stats.no_show">{{ $stats['no_show'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(107,114,128,0.12),rgba(107,114,128,0.06));color:#6b7280;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </div>
            </div>
        </div>

        {{-- Cancelled --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #ef4444;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Cancelled</p>
                    <p class="stat-value" style="color:#dc2626;margin-top:4px;" x-text="stats.cancelled">{{ $stats['cancelled'] }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.06));color:#ef4444;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
        </div>

        {{-- Emergency --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #dc2626;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Emergency</p>
                    <p class="stat-value" style="color:#b91c1c;margin-top:4px;">{{ $emergencyCount }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(220,38,38,0.12),rgba(220,38,38,0.06));color:#dc2626;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
        </div>

        {{-- Avg Wait Time --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #8b5cf6;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Avg Wait</p>
                    <p class="stat-value" style="color:#7c3aed;margin-top:4px;">{{ $avgWaitMinutes }}<span class="text-sm font-normal">m</span></p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(139,92,246,0.06));color:#8b5cf6;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        {{-- Next Serial --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #06b6d4;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium" style="color:var(--text-muted);">Next Serial</p>
                    <p class="stat-value" style="color:#0891b2;margin-top:4px;">{{ $session ? '#' . $nextSerial : '—' }}</p>
                </div>
                <div class="stat-icon" style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,rgba(6,182,212,0.12),rgba(6,182,212,0.06));color:#06b6d4;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Now Calling + Info Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Now Calling --}}
        <div class="lg:col-span-2 dashboard-card animate-card" style="border-left:4px solid #f97316;background:linear-gradient(135deg,rgba(249,115,22,0.06),rgba(245,158,11,0.04));">
            @if($currentCalled)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.08));color:#f97316;animation:pulse 2s infinite;">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider" style="color:#92400e;">Now Calling</p>
                            <p class="text-2xl font-extrabold" style="color:#c2410c;">{{ $currentCalled->patient->name ?? 'N/A' }}</p>
                            <p class="text-xs mt-1" style="color:#92400e;">Called at {{ $currentCalled->called_at->format('h:i A') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-5xl font-extrabold" style="color:#ea580c;">#{{ $currentCalled->serial_number }}</p>
                        @if($currentCalled->priority !== 'normal')
                            <span class="px-3 py-1 rounded-full text-xs font-bold mt-2 inline-block
                                @if($currentCalled->priority === 'emergency') bg-red-100 text-red-700
                                @elseif($currentCalled->priority === 'urgent') bg-orange-100 text-orange-700
                                @elseif($currentCalled->priority === 'vip') bg-purple-100 text-purple-700
                                @endif">
                                {{ strtoupper($currentCalled->priority) }}
                            </span>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center gap-4 py-4">
                    <div class="stat-icon" style="background:linear-gradient(135deg,rgba(107,114,128,0.1),rgba(107,114,128,0.05));color:#9ca3af;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--text-muted);">No patient currently called</p>
                        <p class="text-xs" style="color:var(--text-muted);">Click "Call Next" to call the next patient</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Quick Info --}}
        <div class="dashboard-card animate-card" style="border-top:3px solid #8b5cf6;">
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(139,92,246,0.06));color:#8b5cf6;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <p class="text-sm font-bold" style="color:var(--text-primary);">{{ $doctor->name }}</p>
            </div>
            @if($currentChamber)
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" style="color:#06b6d4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-xs" style="color:var(--text-muted);">{{ is_array($currentChamber) ? ($currentChamber['name'] ?? $currentChamber['location'] ?? 'Chamber') : $currentChamber }}</p>
                </div>
            @endif
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                <p class="text-xs" style="color:var(--text-muted);">Current: <span class="font-bold" style="color:#6366f1;">#{{ $session ? $session->current_serial : '—' }}</span> &middot; Next: <span class="font-bold" style="color:#06b6d4;">#{{ $session ? $nextSerial : '—' }}</span></p>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    @if($session && in_array('call_next', $permissions))
    <div class="dashboard-card animate-card">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-sm font-bold uppercase tracking-wider" style="color:var(--text-muted);">Quick Actions</h3>
            <div class="flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('doctor.smart-serial.call-next', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,0.25);">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                            Call Next
                        </span>
                    </button>
                </form>
                @if($currentCalled)
                    @if(in_array('complete', $permissions))
                    <form method="POST" action="{{ route('doctor.smart-serial.complete', $currentCalled->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 4px 14px rgba(99,102,241,0.25);">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Complete
                            </span>
                        </button>
                    </form>
                    @endif
                    @if(in_array('recall', $permissions))
                    <form method="POST" action="{{ route('doctor.smart-serial.recall', $currentCalled->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 14px rgba(245,158,11,0.25);">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Recall
                            </span>
                        </button>
                    </form>
                    @endif
                    @if(in_array('skip', $permissions))
                    <form method="POST" action="{{ route('doctor.smart-serial.skip', $currentCalled->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all" style="background:linear-gradient(135deg,#6b7280,#4b5563);box-shadow:0 4px 14px rgba(107,114,128,0.25);">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                Skip
                            </span>
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Live Queue List --}}
    @if($session)
    <div class="dashboard-card animate-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold uppercase tracking-wider" style="color:var(--text-muted);">
                Live Queue
                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-bold" style="background:rgba(99,102,241,0.1);color:#6366f1;" x-text="queue.length + ' patients'">{{ $queue->count() }} patients</span>
            </h3>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs" style="color:var(--text-muted);">Auto-refreshing</span>
            </div>
        </div>
        <div class="glass-table">
            <table>
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Patient</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in queue" :key="item.id">
                        <tr :class="{
                            'bg-yellow-50': item.status === 'called',
                            'bg-blue-50': item.status === 'in_consultation',
                            'bg-green-50': item.status === 'completed',
                            'opacity-50': item.status === 'cancelled' || item.status === 'no_show'
                        }">
                            <td><span class="font-bold text-lg" style="color:var(--text-primary);" x-text="'#' + item.serial_number"></span></td>
                            <td><span class="font-medium" style="color:var(--text-primary);" x-text="item.patient?.name || 'N/A'"></span></td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-bold"
                                    :class="{
                                        'bg-red-100 text-red-700': item.priority === 'emergency',
                                        'bg-orange-100 text-orange-700': item.priority === 'urgent',
                                        'bg-purple-100 text-purple-700': item.priority === 'vip',
                                        'bg-gray-100 text-gray-700': item.priority === 'normal'
                                    }" x-text="item.priority.toUpperCase()"></span>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-yellow-100 text-yellow-700': item.status === 'waiting',
                                        'bg-orange-100 text-orange-700': item.status === 'called',
                                        'bg-blue-100 text-blue-700': item.status === 'in_consultation',
                                        'bg-green-100 text-green-700': item.status === 'completed',
                                        'bg-red-100 text-red-700': item.status === 'cancelled',
                                        'bg-gray-200 text-gray-500': item.status === 'no_show'
                                    }" x-text="item.status.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                            </td>
                            <td style="color:var(--text-muted);" x-text="new Date(item.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></td>
                            <td>
                                <div class="flex gap-1 flex-wrap">
                                    <template x-if="item.status === 'waiting' && hasPermission('call_next')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/call'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">Call</button>
                                        </form>
                                    </template>
                                    <template x-if="item.status === 'called' && hasPermission('call_next')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/start-consultation'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-indigo-500 text-white rounded text-xs hover:bg-indigo-600">Start</button>
                                        </form>
                                    </template>
                                    <template x-if="item.status === 'in_consultation' && hasPermission('complete')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/complete'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600">Done</button>
                                        </form>
                                    </template>
                                    <template x-if="item.status !== 'completed' && item.status !== 'cancelled' && hasPermission('cancel_serial')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/cancel'">
                                            @csrf @method('DELETE')
                                            <button class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600">X</button>
                                        </form>
                                    </template>
                                    <template x-if="item.status !== 'completed' && item.status !== 'cancelled' && item.priority !== 'emergency' && hasPermission('emergency')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/emergency'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-800">!</button>
                                        </form>
                                    </template>
                                    <template x-if="(item.status === 'called' || item.status === 'completed') && hasPermission('recall')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/recall'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-yellow-500 text-white rounded text-xs hover:bg-yellow-600">Recall</button>
                                        </form>
                                    </template>
                                    <template x-if="item.status === 'called' && hasPermission('skip')">
                                        <form method="POST" :action="'/doctor/smart-serial/queue/' + item.id + '/skip'">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-gray-500 text-white rounded text-xs hover:bg-gray-600">Skip</button>
                                        </form>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="queue.length === 0">
                        <tr>
                            <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);font-size:14px;">No patients in queue</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- No Session State --}}
    @if(!$session)
    <div class="dashboard-card animate-card p-12 text-center">
        <div class="stat-icon mx-auto mb-4" style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.05));color:#6366f1;">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-xl font-bold" style="color:var(--text-primary);">No Active Session</h3>
        <p class="mt-2" style="color:var(--text-muted);">Start a session from the Smart Serial Queue page to begin managing patients.</p>
        <a href="{{ route('doctor.smart-serial.index') }}" class="btn-gradient inline-block mt-6">Go to Smart Serial Queue</a>
    </div>
    @endif

</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
</style>

<script>
function serialDashboard() {
    return {
        voiceEnabled: true,
        queue: @js($queue->values()->toArray()),
        stats: @js($stats),
        permissions: @js($permissions),
        sessionId: @js($session?->id),
        refreshTimer: null,
        lastCalledId: null,

        init() {
            this.refreshQueue();
            this.refreshTimer = setInterval(() => this.refreshQueue(), 5000);
            const called = this.queue.find(q => q.status === 'called');
            if (called) {
                this.lastCalledId = called.id;
                this.announce(called);
            }
        },

        hasPermission(perm) {
            return this.permissions.includes(perm);
        },

        async refreshQueue() {
            if (!this.sessionId) return;
            try {
                const res = await fetch(`/doctor/smart-serial/${this.sessionId}/status`);
                const data = await res.json();
                if (data.queue) {
                    this.queue = data.queue;
                    this.stats = data.stats;
                    const called = data.queue.find(q => q.status === 'called');
                    if (called && called.id !== this.lastCalledId) {
                        this.lastCalledId = called.id;
                        this.announce(called);
                    }
                    if (!called) this.lastCalledId = null;
                }
            } catch(e) {}
        },

        announce(patient) {
            if (!this.voiceEnabled || !patient || !patient.patient) return;
            const name = patient.patient.name || 'Unknown';
            const serial = patient.serial_number;
            const priority = patient.priority !== 'normal' ? `, ${patient.priority.toUpperCase()} priority` : '';
            const msg = `Now calling number ${serial}, ${name}${priority}. Please come to the chamber.`;
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const u = new SpeechSynthesisUtterance(msg);
                u.lang = 'bn-BD';
                u.rate = 0.9;
                u.pitch = 1;
                window.speechSynthesis.speak(u);
            }
        },

        destroy() {
            if (this.refreshTimer) clearInterval(this.refreshTimer);
        }
    }
}
</script>
@endsection
