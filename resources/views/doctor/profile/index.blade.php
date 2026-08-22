@extends('doctor.layouts.app')

@section('title', 'Profile')

@section('header', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-24 h-24 rounded-full object-cover flex-shrink-0 shadow-lg">
            @else
                <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-3xl flex-shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
            @endif
            <div class="flex-1 text-center sm:text-left">
                <h3 class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mt-2">Doctor</span>
            </div>
            <a href="{{ route('doctor.profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profile
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Personal Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Name</dt>
                    <dd class="text-sm text-gray-900">{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Email</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Phone</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->phone ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Professional Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Specialization</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->specialization ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Qualification</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->qualification ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Experience</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->experience_years ? auth()->user()->experience_years . ' years' : 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- License Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">License Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">License Number</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->license_number ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Work Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Clinic Name</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->clinic_name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Address</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->address ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Visiting Hours</dt>
                    <dd class="text-sm text-gray-700">{{ auth()->user()->visiting_hours ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Change Password Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
        <form method="POST" action="{{ route('doctor.profile.changePassword') }}" class="max-w-md">
            @csrf
            <div class="space-y-4">
                <div>
                    <x-input-label for="current_password" value="Current Password" />
                    <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password" value="New Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm New Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                </div>
            </div>
            <div class="mt-4">
                <x-primary-button>Update Password</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection
