@extends('doctor.layouts.app')

@section('title', 'Edit Appointment')
@section('header', 'Edit Appointment')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-900">Edit Appointment #{{ $appointment->id }}</h3>
        <a href="{{ route('doctor.appointments.show', $appointment) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}">
        @csrf @method('PATCH')

        {{-- Patient --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Patient</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Select Patient *</label>
                <select name="patient_id" required
                        class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    @foreach($patients as $pat)
                        <option value="{{ $pat->id }}" {{ $appointment->patient_id == $pat->id ? 'selected' : '' }}>{{ $pat->name }} ({{ $pat->phone ?? '' }})</option>
                    @endforeach
                </select>
                @error('patient_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Date & Time --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Schedule</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Date & Time *</label>
                <input type="datetime-local" name="appointment_date"
                       value="{{ $appointment->appointment_date->format('Y-m-d\TH:i') }}" required
                       class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                @error('appointment_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Reason --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Reason</span>
            </div>
            <div>
                <textarea name="reason" rows="3" placeholder="Reason for visit..."
                          class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm resize-none">{{ $appointment->reason }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Update Appointment</button>
            <a href="{{ route('doctor.appointments.show', $appointment) }}" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
