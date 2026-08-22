@extends('doctor.layouts.app')

@section('title', 'Appointments')

@section('header', 'Appointments')

@section('content')
<div class="space-y-6" x-data="{ filter: 'all' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2">
            <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 transition-colors">All</button>
            <button @click="filter = 'scheduled'" :class="filter === 'scheduled' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 transition-colors">Scheduled</button>
            <button @click="filter = 'completed'" :class="filter === 'completed' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 transition-colors">Completed</button>
            <button @click="filter = 'cancelled'" :class="filter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 transition-colors">Cancelled</button>
        </div>
        <a href="{{ route('doctor.appointments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Appointment
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($appointments as $appointment)
                    <tr x-show="filter === 'all' || filter === '{{ $appointment->status }}'" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                                    {{ substr($appointment->patient->name, 0, 2) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $appointment->patient->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y g:i A') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $appointment->reason ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($appointment->status == 'scheduled') bg-blue-100 text-blue-800
                                @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                                @elseif($appointment->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($appointment->status == 'no_show') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('doctor.prescriptions.create') }}?patient_id={{ $appointment->patient_id }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-600 border border-emerald-300 rounded-lg hover:bg-emerald-50 hover:border-emerald-400 transition-all duration-200" title="Create Prescription">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Rx
                                </a>
                                @if($appointment->status == 'scheduled')
                                <form action="{{ route('doctor.appointments.complete', $appointment) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-green-600 border border-green-300 rounded-lg hover:bg-green-50 hover:border-green-400 transition-all duration-200">Complete</button>
                                </form>
                                <form action="{{ route('doctor.appointments.cancel', $appointment) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" onclick="return confirm('Cancel this appointment?')" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-red-600 border border-red-300 rounded-lg hover:bg-red-50 hover:border-red-400 transition-all duration-200">Cancel</button>
                                </form>
                                @endif
                                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-indigo-600 border border-indigo-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-400 transition-all duration-200">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No appointments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appointments->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
