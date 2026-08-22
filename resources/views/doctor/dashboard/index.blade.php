@extends('doctor.layouts.app')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Total Patients</p>
                    <p class="stat-value" style="color:var(--text-primary);margin-top:4px;">{{ $totalPatients }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#6366f1, #818cf8);width:60%;"></div>
        </div>
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Today's Appointments</p>
                    <p class="stat-value" style="color:var(--text-primary);margin-top:4px;">{{ $todayAppointments }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(16,185,129,0.06));color:#10b981;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#10b981, #34d399);width:45%;"></div>
        </div>
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Total Prescriptions</p>
                    <p class="stat-value" style="color:var(--text-primary);margin-top:4px;">{{ $totalPrescriptions }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(168,85,247,0.12),rgba(168,85,247,0.06));color:#a855f7;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#a855f7, #c084fc);width:80%;"></div>
        </div>
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-muted);">Active Plan</p>
                    <p class="stat-value" style="color:var(--text-primary);margin-top:4px;">{{ $activePlan ?? 'N/A' }}</p>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));color:#f59e0b;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
            <div style="margin-top:12px;height:3px;border-radius:2px;background:linear-gradient(90deg,#f59e0b, #fbbf24);width:55%;"></div>
        </div>
    </div>

    {{-- Prescription Status Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'investigation_pending']) }}" class="dashboard-card animate-card block hover:scale-[1.02] transition-transform" style="border-left:4px solid #f59e0b;">
            <p class="text-xs font-medium" style="color:#92400e;">Pending Investigations</p>
            <p class="text-2xl font-bold" style="color:#d97706;">{{ $prescriptionStatusCounts['pending_investigations'] }}</p>
        </a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'report_received']) }}" class="dashboard-card animate-card block hover:scale-[1.02] transition-transform" style="border-left:4px solid #3b82f6;">
            <p class="text-xs font-medium" style="color:#1e40af;">Reports Received</p>
            <p class="text-2xl font-bold" style="color:#2563eb;">{{ $prescriptionStatusCounts['report_received'] }}</p>
        </a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'treatment_started']) }}" class="dashboard-card animate-card block hover:scale-[1.02] transition-transform" style="border-left:4px solid #10b981;">
            <p class="text-xs font-medium" style="color:#065f46;">Active Treatments</p>
            <p class="text-2xl font-bold" style="color:#059669;">{{ $prescriptionStatusCounts['active_treatments'] }}</p>
        </a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'follow_up']) }}" class="dashboard-card animate-card block hover:scale-[1.02] transition-transform" style="border-left:4px solid #8b5cf6;">
            <p class="text-xs font-medium" style="color:#4c1d95;">Follow Ups</p>
            <p class="text-2xl font-bold" style="color:#7c3aed;">{{ $prescriptionStatusCounts['follow_ups'] }}</p>
        </a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'completed']) }}" class="dashboard-card animate-card block hover:scale-[1.02] transition-transform" style="border-left:4px solid #6b7280;">
            <p class="text-xs font-medium" style="color:#374151;">Completed</p>
            <p class="text-2xl font-bold" style="color:#4b5563;">{{ $prescriptionStatusCounts['completed'] }}</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick AI Assistant --}}
        <div class="lg:col-span-1 dashboard-card animate-card" x-data="{
            aiQuery: '',
            aiLoading: false,
            aiResponse: null,
            askAI() {
                if (!this.aiQuery.trim() || this.aiLoading) return;
                this.aiLoading = true;
                this.aiResponse = null;
                fetch('{{ route('doctor.ai-assistant.chat') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ message: this.aiQuery })
                })
                .then(res => res.json())
                .then(data => {
                    this.aiResponse = data;
                })
                .catch(() => {
                    this.aiResponse = { reply: 'Sorry, an error occurred. Please try again.' };
                })
                .finally(() => {
                    this.aiLoading = false;
                });
            }
        }">
            <h3 class="text-lg font-semibold" style="color:var(--text-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <svg class="w-5 h-5" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                AI Medical Assistant
            </h3>
            <form @submit.prevent="askAI()">
                <textarea x-model="aiQuery" placeholder="Describe symptoms or ask about medicines, diagnoses, treatments..." class="w-full rounded-lg text-sm" style="padding:12px;border:1px solid rgba(148,163,184,0.25);background:rgba(255,255,255,0.5);backdrop-filter:blur(8px);border-radius:10px;outline:none;transition:all 0.2s;resize:vertical;" rows="3" :disabled="aiLoading"></textarea>
                <div class="mt-3 flex justify-between items-center">
                    <a href="{{ route('doctor.ai-assistant') }}" class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors">Open Full Assistant &rarr;</a>
                    <button type="submit" :disabled="aiLoading || !aiQuery.trim()" class="btn-gradient disabled:opacity-50">
                        <span x-show="!aiLoading">Ask AI</span>
                        <span x-show="aiLoading">Thinking...</span>
                    </button>
                </div>
            </form>
            <div x-show="aiResponse" x-cloak class="mt-4 p-4 rounded-lg" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
                <p class="text-sm whitespace-pre-wrap" style="color:#4f46e5;" x-text="aiResponse?.reply || ''"></p>
                <template x-if="aiResponse?.warnings?.length > 0">
                    <div class="mt-2 p-2 rounded bg-red-50 border border-red-200">
                        <template x-for="(w, wi) in aiResponse.warnings" :key="wi">
                            <p class="text-xs text-red-600" x-text="w"></p>
                        </template>
                    </div>
                </template>
                <p class="mt-2 text-[10px] text-gray-400 italic">AI is a clinical decision support tool. Final decisions rest with the physician.</p>
            </div>
        </div>

        {{-- Today's Follow-ups --}}
        <div class="lg:col-span-1 dashboard-card animate-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color:var(--text-primary);">Today's Follow-ups</h3>
                <a href="{{ route('doctor.prescriptions.index', ['status' => 'follow_up']) }}" class="text-sm font-medium transition-all" style="color:#7c3aed;padding:6px 14px;border-radius:8px;background:rgba(139,92,246,0.08);">View All &rarr;</a>
            </div>
            <div class="glass-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Follow-up Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayFollowUps as $rx)
                        <tr>
                            <td><span class="font-medium">{{ $rx->patient->name }}</span></td>
                            <td style="color:var(--text-muted);">{{ $rx->follow_up_date ? \Carbon\Carbon::parse($rx->follow_up_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                    Follow Up
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:24px;color:var(--text-muted);font-size:14px;">No follow-ups scheduled for today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Appointments --}}
        <div class="lg:col-span-1 dashboard-card animate-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold" style="color:var(--text-primary);">Recent Appointments</h3>
                <a href="{{ route('doctor.appointments.index') }}" class="text-sm font-medium transition-all" style="color:#6366f1;padding:6px 14px;border-radius:8px;background:rgba(99,102,241,0.08);">View All &rarr;</a>
            </div>
            <div class="glass-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAppointments as $appointment)
                        <tr>
                            <td><span class="font-medium">{{ $appointment->patient->name }}</span></td>
                            <td style="color:var(--text-muted);">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y g:i A') }}</td>
                            <td>
                                <span class="status-badge
                                    @if($appointment->status == 'scheduled')" style="background:rgba(59,130,246,0.1);color:#2563eb;"
                                    @elseif($appointment->status == 'completed')" style="background:rgba(16,185,129,0.1);color:#059669;"
                                    @elseif($appointment->status == 'cancelled')" style="background:rgba(239,68,68,0.1);color:#dc2626;"
                                    @else" style="background:rgba(100,116,139,0.1);color:#475569;"
                                    @endif>
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:32px;color:var(--text-muted);font-size:14px;">No recent appointments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
