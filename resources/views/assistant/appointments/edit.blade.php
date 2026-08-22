@extends('assistant.layouts.app')

@section('title', 'Edit Appointment')
@section('header', 'Edit Appointment')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">Edit Appointment #{{ $appointment->id }}</h3>
        <a href="{{ route('assistant.appointments.show', $appointment) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('assistant.appointments.update', $appointment) }}" x-data="appointmentForm()">
        @csrf @method('PATCH')
        <div class="dashboard-card space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Doctor *</label>
                <select name="doctor_id" x-model="doctorId" @change="loadAvailability()" required
                        class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ $appointment->doctor_id == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                    @endforeach
                </select>
                @error('doctor_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Patient *</label>
                <select name="patient_id" required
                        class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @foreach($patients as $pat)
                        <option value="{{ $pat->id }}" {{ $appointment->patient_id == $pat->id ? 'selected' : '' }}>{{ $pat->name }} ({{ $pat->phone ?? '' }})</option>
                    @endforeach
                </select>
                @error('patient_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Date & Time *</label>
                <input type="datetime-local" name="appointment_date"
                       value="{{ $appointment->appointment_date->format('Y-m-d\TH:i') }}" required
                       class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                @error('appointment_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Reason</label>
                <textarea name="reason" rows="3"
                          class="w-full px-3 py-2 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none">{{ $appointment->reason }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-white/20">
                <button type="submit" class="btn-gradient">Update Appointment</button>
                <a href="{{ route('assistant.appointments.show', $appointment) }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-white/30 transition-colors">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
