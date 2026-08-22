@extends('doctor.layouts.app')

@section('title', "Today's Appointments")

@section('header', "Today's Appointments")

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::now()->format('l, F d, Y') }}</h3>
            <p class="text-sm text-gray-500">{{ $appointments->count() }} appointment(s) scheduled for today</p>
        </div>
        <a href="{{ route('doctor.appointments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Appointment
        </a>
    </div>

    <div class="space-y-4">
        @forelse($appointments as $appointment)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="text-center flex-shrink-0">
                        <p class="text-lg font-bold text-indigo-600">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('g:i A') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg flex-shrink-0">
                        {{ substr($appointment->patient->name, 0, 2) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $appointment->patient->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $appointment->reason ?? 'No reason provided' }}</p>
                        @if($appointment->notes)
                        <p class="text-xs text-gray-400 mt-1">{{ Str::limit($appointment->notes, 60) }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($appointment->status == 'scheduled') bg-blue-100 text-blue-800
                        @elseif($appointment->status == 'completed') bg-green-100 text-green-800
                        @elseif($appointment->status == 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                    </span>
                    <div class="flex items-center gap-2">
                        @if($appointment->status == 'scheduled')
                        <form action="{{ route('doctor.appointments.complete', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">Complete</button>
                        </form>
                        <form action="{{ route('doctor.appointments.cancel', $appointment) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">Cancel</button>
                        </form>
                        @endif
                        <a href="{{ route('doctor.prescriptions.create') }}?patient_id={{ $appointment->patient_id }}" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors">Prescribe</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 text-lg font-medium">No appointments scheduled for today</p>
            <p class="text-gray-400 text-sm mt-1">Enjoy your day!</p>
            <a href="{{ route('doctor.appointments.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Schedule an Appointment</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
