@extends('doctor.layouts.app')

@section('title', 'Smart Serial Dashboard')

@section('header', 'Smart Serial Dashboard')

@section('content')
<div class="space-y-6">

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
                <a href="{{ route('doctor.smart-serial.index') }}" class="text-sm font-medium transition-all" style="color:#6366f1;padding:6px 14px;border-radius:8px;background:rgba(99,102,241,0.08);">Open Queue &rarr;</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Primary Stats Row --}}
    <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-3 gap-4">
        {{-- Today's Total --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Today's Total</p>
                    <p class="stat-value" style="color:#6366f1;margin-top:4px;">{{ $stats['total'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#6366f1, #818cf8);width:{{ $stats['total'] > 0 ? 100 : 0 }}%;"></div>
        </div>

        {{-- Waiting --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #f59e0b;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Waiting</p>
                    <p class="stat-value" style="color:#d97706;margin-top:4px;">{{ $stats['waiting'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));color:#f59e0b;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#f59e0b, #fbbf24);width:{{ $stats['total'] > 0 ? ($stats['waiting'] / $stats['total'] * 100) : 0 }}%;"></div>
        </div>

        {{-- Emergency --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #ef4444;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Emergency</p>
                    <p class="stat-value" style="color:#dc2626;margin-top:4px;">{{ $emergencyCount }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.06));color:#ef4444;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#ef4444, #f87171);width:{{ $stats['total'] > 0 ? ($emergencyCount / $stats['total'] * 100) : 0 }}%;"></div>
        </div>
    </div>

    {{-- Secondary Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Preparing (Called) --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #f97316;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Preparing</p>
                    <p class="stat-value" style="color:#ea580c;margin-top:4px;">{{ $stats['called'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(249,115,22,0.12),rgba(249,115,22,0.06));color:#f97316;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#f97316, #fb923c);width:{{ $stats['total'] > 0 ? ($stats['called'] / $stats['total'] * 100) : 0 }}%;"></div>
        </div>

        {{-- Inside (In Consultation) --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #3b82f6;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Inside</p>
                    <p class="stat-value" style="color:#2563eb;margin-top:4px;">{{ $stats['in_consultation'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(59,130,246,0.06));color:#3b82f6;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#3b82f6, #60a5fa);width:{{ $stats['total'] > 0 ? ($stats['in_consultation'] / $stats['total'] * 100) : 0 }}%;"></div>
        </div>

        {{-- Completed --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #10b981;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Completed</p>
                    <p class="stat-value" style="color:#059669;margin-top:4px;">{{ $stats['completed'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(16,185,129,0.06));color:#10b981;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#10b981, #34d399);width:{{ $stats['total'] > 0 ? ($stats['completed'] / $stats['total'] * 100) : 0 }}%;"></div>
        </div>

        {{-- Skipped / Cancelled --}}
        <div class="dashboard-card animate-card" style="border-left:4px solid #6b7280;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Skipped</p>
                    <p class="stat-value" style="color:#4b5563;margin-top:4px;">{{ $stats['no_show'] }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(107,114,128,0.12),rgba(107,114,128,0.06));color:#6b7280;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#6b7280, #9ca3af);width:{{ $stats['total'] > 0 ? ($stats['no_show'] / $stats['total'] * 100) : 0 }}%;"></div>
        </div>
    </div>

    {{-- Current Status Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Current Serial --}}
        <div class="dashboard-card animate-card text-center" style="border-top:3px solid #6366f1;">
            <div class="stat-icon mx-auto mb-3" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Current Serial</p>
            <p class="text-4xl font-extrabold mt-2" style="color:#6366f1;">{{ $session ? $session->current_serial : '—' }}</p>
        </div>

        {{-- Next Patient --}}
        <div class="dashboard-card animate-card text-center" style="border-top:3px solid #10b981;">
            <div class="stat-icon mx-auto mb-3" style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(16,185,129,0.06));color:#10b981;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Next Patient</p>
            @if($nextPatient)
                <p class="text-lg font-bold mt-2" style="color:var(--text-primary);">{{ $nextPatient->patient->name ?? 'N/A' }}</p>
                <p class="text-xs mt-1" style="color:var(--text-muted);">Serial #{{ $nextPatient->serial_number }}</p>
            @else
                <p class="text-lg font-bold mt-2" style="color:var(--text-muted);">—</p>
                <p class="text-xs mt-1" style="color:var(--text-muted);">No one waiting</p>
            @endif
        </div>

        {{-- Current Doctor --}}
        <div class="dashboard-card animate-card text-center" style="border-top:3px solid #8b5cf6;">
            <div class="stat-icon mx-auto mb-3" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(139,92,246,0.06));color:#8b5cf6;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Current Doctor</p>
            <p class="text-lg font-bold mt-2" style="color:var(--text-primary);">{{ $doctor->name }}</p>
            @if($doctor->specialization)
                <p class="text-xs mt-1" style="color:var(--text-muted);">{{ $doctor->specialization }}</p>
            @endif
        </div>

        {{-- Current Chamber --}}
        <div class="dashboard-card animate-card text-center" style="border-top:3px solid #06b6d4;">
            <div class="stat-icon mx-auto mb-3" style="background:linear-gradient(135deg,rgba(6,182,212,0.12),rgba(6,182,212,0.06));color:#06b6d4;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--text-muted);">Current Chamber</p>
            @if($currentChamber)
                <p class="text-lg font-bold mt-2" style="color:var(--text-primary);">
                    {{ is_array($currentChamber) ? ($currentChamber['name'] ?? $currentChamber['location'] ?? 'Chamber') : $currentChamber }}
                </p>
                @if(is_array($currentChamber) && isset($currentChamber['location']))
                    <p class="text-xs mt-1" style="color:var(--text-muted);">{{ $currentChamber['location'] }}</p>
                @endif
            @else
                <p class="text-lg font-bold mt-2" style="color:var(--text-muted);">—</p>
                <p class="text-xs mt-1" style="color:var(--text-muted);">No chamber set</p>
            @endif
        </div>
    </div>

    {{-- Currently Calling Banner --}}
    @if($currentCalled)
    <div class="dashboard-card animate-card" style="border-left:4px solid #f97316;background:linear-gradient(135deg,rgba(249,115,22,0.06),rgba(245,158,11,0.04));">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.08));color:#f97316;animation:pulse 2s infinite;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider" style="color:#92400e;">Now Calling</p>
                    <p class="text-2xl font-extrabold" style="color:#c2410c;">{{ $currentCalled->patient->name ?? 'N/A' }}</p>
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
    </div>
    @endif

    {{-- Cancelled Info --}}
    @if($stats['cancelled'] > 0)
    <div class="dashboard-card animate-card" style="border-left:4px solid #ef4444;">
        <div class="flex items-center gap-3">
            <div class="stat-icon" style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.06));color:#ef4444;width:40px;height:40px;border-radius:10px;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold" style="color:#991b1b;">{{ $stats['cancelled'] }} patient{{ $stats['cancelled'] > 1 ? 's' : '' }} cancelled today</p>
            </div>
        </div>
    </div>
    @endif

    {{-- No Session State --}}
    @if(!$session)
    <div class="dashboard-card animate-card p-12 text-center">
        <div class="stat-icon mx-auto mb-4" style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.05));color:#6366f1;">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
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
@endsection
