@extends('assistant.layouts.app')

@section('title', 'Register Patient')
@section('header', 'Register New Patient')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">New Patient Registration</h3>
        <a href="{{ route('assistant.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('assistant.patients.store') }}">
        @csrf

        {{-- Doctor Assignment --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Doctor Assignment</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Assign to Doctor *</label>
                <select name="doctor_id" required
                        class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    <option value="">Select Doctor</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }} - {{ $doc->specialization ?? 'General' }}</option>
                    @endforeach
                </select>
                @error('doctor_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Personal Information</span>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Enter patient name">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Enter phone number">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="Enter email address">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Age (yrs) *</label>
                        <input type="number" name="age" value="{{ old('age') }}" min="0" max="150" required
                               class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g. 35">
                        @error('age') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Gender</label>
                        <select name="gender" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Blood Group</label>
                        <select name="blood_group" class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="">Select</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Weight (kg)</label>
                        <input type="text" name="weight" value="{{ old('weight') }}"
                               class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none" placeholder="e.g. 65">
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Address</span>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Address</label>
                <textarea name="address" rows="2" placeholder="Enter full address..."
                          class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none">{{ old('address') }}</textarea>
            </div>
        </div>

        {{-- Quick Appointment --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Quick Action</span>
            </div>
            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-white/40 transition-colors">
                <input type="checkbox" name="and_appointment" value="1" class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Also book an appointment after registration</span>
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-gradient">Register Patient</button>
            <a href="{{ route('assistant.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-white/30 transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
