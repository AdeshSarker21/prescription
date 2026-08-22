@extends('doctor.layouts.app')

@section('title', 'Add New Patient')

@section('header', 'Add New Patient')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('doctor.patients.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Full Name *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="age" value="Age (yrs)" />
                    <x-text-input id="age" name="age" type="number" min="0" max="150" class="mt-1 block w-full" :value="old('age')" />
                    <x-input-error :messages="$errors->get('age')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Gender</option>
                        <option value="male" @selected(old('gender') == 'male')>Male</option>
                        <option value="female" @selected(old('gender') == 'female')>Female</option>
                        <option value="other" @selected(old('gender') == 'other')>Other</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address') }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-1" />
            </div>

            <div class="mt-6">
                <x-input-label for="medical_history" value="Medical History" />
                <textarea id="medical_history" name="medical_history" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('medical_history') }}</textarea>
                <x-input-error :messages="$errors->get('medical_history')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3 mt-8">
                <x-primary-button>Save Patient</x-primary-button>
                <a href="{{ route('doctor.patients.index') }}">
                    <x-secondary-button type="button">Cancel</x-secondary-button>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
