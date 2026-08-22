@extends('doctor.layouts.app')

@section('title', 'Edit Patient')

@section('header', 'Edit: ' . $patient->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('doctor.patients.update', $patient) }}">
            @csrf @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Full Name *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $patient->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $patient->email)" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $patient->phone)" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="emergency_contact" value="Emergency Contact" />
                    <x-text-input id="emergency_contact" name="emergency_contact" type="text" class="mt-1 block w-full" :value="old('emergency_contact', $patient->emergency_contact)" />
                    <x-input-error :messages="$errors->get('emergency_contact')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="age" value="Age (yrs)" />
                    <x-text-input id="age" name="age" type="number" min="0" max="150" class="mt-1 block w-full" :value="old('age', $patient->date_of_birth?->age)" />
                    <x-input-error :messages="$errors->get('age')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Gender</option>
                        <option value="male" @selected(old('gender', $patient->gender) == 'male')>Male</option>
                        <option value="female" @selected(old('gender', $patient->gender) == 'female')>Female</option>
                        <option value="other" @selected(old('gender', $patient->gender) == 'other')>Other</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="blood_group" value="Blood Group" />
                    <select id="blood_group" name="blood_group" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Blood Group</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group', $patient->blood_group) == $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('blood_group')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="height" value="Height (cm)" />
                        <x-text-input id="height" name="height" type="number" step="0.1" class="mt-1 block w-full" :value="old('height', $patient->height)" />
                        <x-input-error :messages="$errors->get('height')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="weight" value="Weight (kg)" />
                        <x-text-input id="weight" name="weight" type="number" step="0.1" class="mt-1 block w-full" :value="old('weight', $patient->weight)" />
                        <x-input-error :messages="$errors->get('weight')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="occupation" value="Occupation" />
                    <x-text-input id="occupation" name="occupation" type="text" class="mt-1 block w-full" :value="old('occupation', $patient->occupation)" />
                    <x-input-error :messages="$errors->get('occupation')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="marital_status" value="Marital Status" />
                    <select id="marital_status" name="marital_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select</option>
                        <option value="single" @selected(old('marital_status', $patient->marital_status) == 'single')>Single</option>
                        <option value="married" @selected(old('marital_status', $patient->marital_status) == 'married')>Married</option>
                        <option value="divorced" @selected(old('marital_status', $patient->marital_status) == 'divorced')>Divorced</option>
                        <option value="widowed" @selected(old('marital_status', $patient->marital_status) == 'widowed')>Widowed</option>
                    </select>
                    <x-input-error :messages="$errors->get('marital_status')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="national_id" value="National ID" />
                    <x-text-input id="national_id" name="national_id" type="text" class="mt-1 block w-full" :value="old('national_id', $patient->national_id)" />
                    <x-input-error :messages="$errors->get('national_id')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $patient->address) }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-1" />
            </div>

            <div class="mt-6">
                <x-input-label for="medical_history" value="Medical History" />
                <textarea id="medical_history" name="medical_history" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('medical_history', $patient->medical_history) }}</textarea>
                <x-input-error :messages="$errors->get('medical_history')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3 mt-8">
                <x-primary-button>Update Patient</x-primary-button>
                <a href="{{ route('doctor.patients.show', $patient) }}">
                    <x-secondary-button type="button">Cancel</x-secondary-button>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
