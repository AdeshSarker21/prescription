@extends('doctor.layouts.app')

@section('title', 'Appointment Details')
@section('header', 'Appointment Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">Appointment #{{ $appointment->id }}</h3>
        <a href="{{ route('doctor.appointments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        {{-- Status Banner --}}
        @php
            $statusColors = [
                'scheduled' => 'from-blue-500 to-blue-600',
                'completed' => 'from-emerald-500 to-emerald-600',
                'cancelled' => 'from-red-500 to-red-600',
                'no_show' => 'from-amber-500 to-amber-600',
            ];
        @endphp
        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r {{ $statusColors[$appointment->status] ?? 'from-gray-500 to-gray-600' }} text-white">
            <div>
                <p class="text-sm font-medium opacity-90 text-black">Status</p>
                <p class="text-lg font-bold text-blackope">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</p>
            </div>
            @if($appointment->status === 'scheduled')
            <div class="flex gap-2">
                <form method="POST" action="{{ route('doctor.appointments.complete', $appointment) }}">
                    @csrf @method('PATCH')
                    <button class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg">Complete</button>
                </form>
                <form method="POST" action="{{ route('doctor.appointments.cancel', $appointment) }}">
                    @csrf @method('PATCH')
                    <button class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                </form>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</p>
                <p class="text-sm font-semibold text-gray-800 mt-1">{{ $appointment->patient->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">{{ $appointment->patient->phone ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</p>
                <p class="text-sm font-semibold text-gray-800 mt-1">{{ $appointment->appointment_date->format('F d, Y') }}</p>
                <p class="text-xs text-gray-500">{{ $appointment->appointment_date->format('h:i A') }}</p>
            </div>
        </div>

        @if($appointment->reason)
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</p>
            <p class="text-sm text-gray-700 mt-1">{{ $appointment->reason }}</p>
        </div>
        @endif

        <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
            @if($appointment->status === 'scheduled')
            <a href="{{ route('doctor.appointments.edit', $appointment) }}" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Edit</a>
            @endif
            <a href="{{ route('doctor.appointments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-50 transition-colors">Back to List</a>
        </div>
    </div>
</div>
@endsection
