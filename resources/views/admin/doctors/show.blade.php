@extends('admin.layouts.app')

@section('title', __('Doctor Details - Admin'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.doctors.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Doctors') }}
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white/90">{{ __('Doctor Details') }}</h1>
                <p class="text-sm text-white/50 mt-1">{{ __('Profile and information for :name.', ['name' => $user->name]) }} @if($user->name_bn) <span class="text-white/40">({{ $user->name_bn }})</span> @endif</p>
            </div>
            <a href="{{ route('admin.doctors.edit', $user) }}" class="btn-gradient">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ __('Edit Doctor') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Card --}}
        <div class="glass-card-static p-6 text-center">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->locale('name') }}" class="w-20 h-20 rounded-full object-cover border-2 border-white/5 mx-auto">
            <h2 class="mt-4 text-xl font-bold text-white/90">{{ $user->locale('name') }}</h2>
            <p class="text-sm text-white/50">{{ $user->locale('specialization') ?: __('General Practitioner') }}</p>
            <div class="mt-4 flex justify-center gap-2">
                <span class="status-badge
                    {{ $user->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                    {{ $user->status === 'inactive' ? 'bg-amber-500/20 text-amber-400' : '' }}
                    {{ $user->status === 'suspended' ? 'bg-red-500/20 text-red-400' : '' }}">
                    {{ ucfirst($user->status) }}
                </span>
                <span class="status-badge
                    {{ $user->is_approved ? 'bg-blue-500/20 text-blue-400' : 'bg-white/10 text-white/50' }}">
                    {{ $user->is_approved ? __('Approved') : __('Pending') }}
                </span>
            </div>
            @if ($user->activePlan())
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-xs text-white/50 uppercase tracking-wider">{{ __('Current Plan') }}</p>
                    <p class="mt-1 text-sm font-semibold text-white/90">{{ $user->activePlan()->name }}</p>
                </div>
            @endif
            <div class="mt-4 pt-4 border-t border-white/5">
                <p class="text-3xl font-bold text-indigo-400">{{ $user->patient_count }}</p>
                <p class="text-xs text-white/50 uppercase tracking-wider mt-1">{{ __('Total Patients') }}</p>
            </div>
        </div>

        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Contact --}}
            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">{{ __('Contact Information') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Email') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Phone') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Professional --}}
            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">{{ __('Professional Information') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Specialization') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->specialization ?: '—' }}@if($user->specialization_bn) <span class="text-white/40 text-xs">({{ $user->specialization_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Qualification') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->qualification ?: '—' }}@if($user->qualification_bn) <span class="text-white/40 text-xs">({{ $user->qualification_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('License Number') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->license_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Experience') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->experience_years ? $user->experience_years . ' years' : '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Work / Prescription Settings --}}
            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">{{ __('Work & Prescription Settings') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Clinic Name') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->clinic_name ?: '—' }}@if($user->clinic_name_bn) <span class="text-white/40 text-xs">({{ $user->clinic_name_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Visiting Hours') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->visiting_hours ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Designation Title') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->designation_title ?: '—' }}@if($user->designation_title_bn) <span class="text-white/40 text-xs">({{ $user->designation_title_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Affiliated Hospital') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->affiliated_hospital ?: '—' }}@if($user->affiliated_hospital_bn) <span class="text-white/40 text-xs">({{ $user->affiliated_hospital_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Sub-Specialties') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">
                            @php
                                $enSubs = $user->sub_specialties ?? [];
                                $bnSubs = $user->sub_specialties_bn ?? [];
                            @endphp
                            {{ $enSubs ? implode(', ', $enSubs) : '—' }}
                            @if($bnSubs) <span class="text-white/40 text-xs">({{ implode(', ', $bnSubs) }})</span>@endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Emergency Contact') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->emergency_contact ?: '—' }}@if($user->emergency_contact_bn) <span class="text-white/40 text-xs">({{ $user->emergency_contact_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Prescription Heading') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->prescription_heading ?: '—' }}@if($user->prescription_heading_bn) <span class="text-white/40 text-xs">({{ $user->prescription_heading_bn }})</span>@endif</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Prescription Slogan') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->prescription_slogan ?: '—' }}@if($user->prescription_slogan_bn) <span class="text-white/40 text-xs">({{ $user->prescription_slogan_bn }})</span>@endif</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Chambers') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">
                            @if($user->chambers && count($user->chambers) > 0)
                                <ul class="list-disc list-inside">
                                @foreach($user->chambers as $chamber)
                                    <li>{{ $chamber['name'] ?? '' }} — {{ $chamber['phone'] ?? '' }} @if(!empty($chamber['booking_hotline'])) (Booking: {{ $chamber['booking_hotline'] }}) @endif</li>
                                @endforeach
                                </ul>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-white/50 uppercase tracking-wider">{{ __('Address') }}</dt>
                        <dd class="mt-1 text-sm font-medium text-white/90">{{ $user->address ?: '—' }}@if($user->address_bn) <span class="text-white/40 text-xs">({{ $user->address_bn }})</span>@endif</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
