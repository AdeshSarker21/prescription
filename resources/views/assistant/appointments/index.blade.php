@extends('assistant.layouts.app')

@section('title', 'Appointments')
@section('header', 'Appointments')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h3 class="text-lg font-bold text-gray-800">All Appointments</h3>
        <a href="{{ route('assistant.appointments.create') }}" class="btn-gradient text-sm">+ New Appointment</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="dashboard-card">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search Patient</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone, or ID..."
                       class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">All</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Doctor</label>
                <select name="doctor_id" class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">All</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Filter</button>
            <a href="{{ route('assistant.appointments.index') }}" class="px-4 py-2 text-xs font-medium text-gray-600 bg-white/40 rounded-lg hover:bg-white/60">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-table">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $apt)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-400 to-rose-500 flex items-center justify-center text-white text-xs font-bold">
                                {{ substr($apt->patient->name ?? 'N', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $apt->patient->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $apt->patient->phone ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-600">Dr. {{ $apt->doctor->name ?? 'N/A' }}</td>
                    <td class="text-sm text-gray-600">{{ $apt->appointment_date->format('M d, Y h:i A') }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'scheduled' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'no_show' => 'bg-amber-100 text-amber-700',
                            ];
                        @endphp
                        <span class="status-badge {{ $statusColors[$apt->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('assistant.appointments.show', $apt) }}" class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if($apt->status === 'scheduled')
                            <a href="{{ route('assistant.appointments.edit', $apt) }}" class="p-1.5 rounded-lg text-gray-600 hover:bg-gray-100" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-white/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-36 glass-dropdown py-1 z-40" x-cloak>
                                    <form method="POST" action="{{ route('assistant.appointments.complete', $apt) }}">
                                        @csrf @method('PATCH')
                                        <button class="block w-full text-left px-3 py-2 text-xs text-emerald-700 hover:bg-emerald-50">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('assistant.appointments.cancel', $apt) }}">
                                        @csrf @method('PATCH')
                                        <button class="block w-full text-left px-3 py-2 text-xs text-red-700 hover:bg-red-50">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm">No appointments found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $appointments->withQueryString()->links() }}
</div>
@endsection
