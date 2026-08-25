@extends('assistant.layouts.app')

@section('title', 'Serial History')
@section('header', 'Serial History')

@section('content')
<div class="space-y-6">

    {{-- Doctor Selector --}}
    @if($doctors->count() > 1)
    <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
        <div class="flex items-center gap-4 flex-wrap">
            <span class="text-sm font-semibold" style="color:var(--text-muted);">Select Doctor:</span>
            @foreach($doctors as $doc)
                <a href="{{ route('assistant.smart-serial.history', ['doctor_id' => $doc->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($selectedDoctorId == $doc->id) ? 'bg-blue-600 text-white shadow-md' : 'bg-white/50 text-gray-600 hover:bg-white/70' }}">
                    {{ $doc->name }}@if($doc->clinic_name) - {{ $doc->clinic_name }}@endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Serial History</h1>
            <p class="text-gray-500 mt-1">View past sessions and queue history</p>
        </div>
        <div class="flex gap-2 items-center">
            <a href="{{ route('assistant.smart-serial.index', ['doctor_id' => $selectedDoctorId]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Back to Queue</a>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="dashboard-card animate-card">
        <form method="GET" action="{{ route('assistant.smart-serial.history') }}" class="flex gap-3 items-end flex-wrap">
            <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
            <div>
                <label class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="mt-1 border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="mt-1 border rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Filter</button>
        </form>
    </div>

    @if(!$selectedDoctorId)
    <div class="dashboard-card animate-card p-12 text-center">
        <p class="text-gray-400">Select a doctor to view serial history.</p>
    </div>
    @else

    {{-- History List --}}
    @forelse($history as $session)
    <div class="dashboard-card animate-card">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <div class="stat-icon" style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(99,102,241,0.06));color:#6366f1;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold" style="color:var(--text-primary);">
                        {{ $session->session_date->format('d M, Y') }}
                        @if($session->session_label) <span class="text-sm font-normal text-gray-500">({{ $session->session_label }})</span> @endif
                    </p>
                    <p class="text-xs" style="color:var(--text-muted);">
                        Started {{ $session->started_at ? $session->started_at->format('h:i A') : 'N/A' }}
                        @if($session->closed_at) &middot; Closed {{ $session->closed_at->format('h:i A') }} @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    @if($session->status === 'active') bg-green-100 text-green-700
                    @elseif($session->status === 'paused') bg-yellow-100 text-yellow-700
                    @else bg-gray-100 text-gray-600
                    @endif">
                    {{ ucfirst($session->status) }}
                </span>
                <span class="text-sm font-medium" style="color:var(--text-muted);">
                    {{ $session->total_patients }} patients
                </span>
            </div>
        </div>

        @if($session->patientQueues->count() > 0)
        <div class="glass-table">
            <table>
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Patient</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Called At</th>
                        <th>Completed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->patientQueues as $q)
                    <tr class="{{ $q->status === 'completed' ? 'bg-green-50/50' : ($q->status === 'cancelled' ? 'opacity-60' : '') }}">
                        <td>
                            <span class="font-bold" style="color:var(--text-primary);">{{ $q->formatted_serial ?? str_pad($q->serial_number, 3, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <span class="font-medium" style="color:var(--text-primary);">{{ $q->patient->name ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                @if($q->priority === 'emergency') bg-red-100 text-red-700
                                @elseif($q->priority === 'urgent') bg-orange-100 text-orange-700
                                @elseif($q->priority === 'vip') bg-purple-100 text-purple-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                {{ strtoupper($q->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($q->status === 'completed') bg-green-100 text-green-700
                                @elseif($q->status === 'cancelled') bg-red-100 text-red-700
                                @elseif($q->status === 'skipped') bg-gray-200 text-gray-500
                                @elseif($q->status === 'waiting') bg-yellow-100 text-yellow-700
                                @elseif($q->status === 'calling') bg-orange-100 text-orange-700
                                @elseif($q->status === 'inside') bg-blue-100 text-blue-700
                                @elseif($q->status === 'preparing') bg-purple-100 text-purple-700
                                @endif">
                                {{ ucfirst($q->status) }}
                            </span>
                        </td>
                        <td class="text-xs" style="color:var(--text-muted);">
                            {{ $q->called_at ? $q->called_at->format('h:i A') : '—' }}
                        </td>
                        <td class="text-xs" style="color:var(--text-muted);">
                            {{ $q->completed_at ? $q->completed_at->format('h:i A') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">No patients in this session.</p>
        @endif
    </div>
    @empty
    <div class="dashboard-card animate-card p-12 text-center">
        <p class="text-gray-400">No sessions found for the selected date range.</p>
    </div>
    @endforelse

    <div class="d-flex justify-content-center">
        {{ $history->withQueryString()->links() }}
    </div>

    @endif
</div>
@endsection
