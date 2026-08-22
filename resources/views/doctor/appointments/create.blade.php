@extends('doctor.layouts.app')

@section('title', 'New Appointment')

@section('header', 'New Appointment')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('doctor.appointments.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="patient_id" value="Patient *" />
                    <select id="patient_id" name="patient_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Patient</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->name }} ({{ $patient->phone ?? 'No phone' }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('patient_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="appointment_date" value="Appointment Date & Time *" />
                    <x-text-input id="appointment_date" name="appointment_date" type="datetime-local" class="mt-1 block w-full" :value="old('appointment_date')" required />
                    <x-input-error :messages="$errors->get('appointment_date')" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="reason" value="Reason *" />
                    <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                    <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-3 mt-8">
                <x-primary-button>Create Appointment</x-primary-button>
                <a href="{{ route('doctor.appointments.index') }}">
                    <x-secondary-button type="button">Cancel</x-secondary-button>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
