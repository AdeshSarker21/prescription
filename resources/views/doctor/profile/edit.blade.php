@extends('doctor.layouts.app')

@section('title', 'Edit Profile')

@section('header', 'Edit Profile')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ avatarPreview: null }">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Avatar --}}
            <div class="mb-8">
                <x-input-label value="Profile Photo" />
                <div class="mt-2 flex items-center gap-6">
                    <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-3xl overflow-hidden">
                        <template x-if="!avatarPreview">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ substr(auth()->user()->name, 0, 2) }}</span>
                            @endif
                        </template>
                        <img x-show="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" x-on:change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { avatarPreview = e.target.result; }; reader.readAsDataURL(file); }">
                        <label for="avatar" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 cursor-pointer">Choose Photo</label>
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Full Name *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', auth()->user()->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="Email *" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', auth()->user()->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', auth()->user()->phone)" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="specialization" value="Specialization" />
                    <x-text-input id="specialization" name="specialization" type="text" class="mt-1 block w-full" :value="old('specialization', auth()->user()->specialization)" />
                    <x-input-error :messages="$errors->get('specialization')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="qualification" value="Qualification" />
                    <x-text-input id="qualification" name="qualification" type="text" class="mt-1 block w-full" :value="old('qualification', auth()->user()->qualification)" />
                    <x-input-error :messages="$errors->get('qualification')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="license_number" value="License Number" />
                    <x-text-input id="license_number" name="license_number" type="text" class="mt-1 block w-full" :value="old('license_number', auth()->user()->license_number)" />
                    <x-input-error :messages="$errors->get('license_number')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="experience_years" value="Experience (Years)" />
                    <x-text-input id="experience_years" name="experience_years" type="number" class="mt-1 block w-full" :value="old('experience_years', auth()->user()->experience_years)" />
                    <x-input-error :messages="$errors->get('experience_years')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="clinic_name" value="Clinic Name" />
                    <x-text-input id="clinic_name" name="clinic_name" type="text" class="mt-1 block w-full" :value="old('clinic_name', auth()->user()->clinic_name)" />
                    <x-input-error :messages="$errors->get('clinic_name')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', auth()->user()->address) }}</textarea>
                <x-input-error :messages="$errors->get('address')" class="mt-1" />
            </div>

            <div class="mt-6">
                <x-input-label for="visiting_hours" value="Visiting Hours" />
                <textarea id="visiting_hours" name="visiting_hours" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="e.g. Mon-Fri: 9:00 AM - 5:00 PM">{{ old('visiting_hours', auth()->user()->visiting_hours) }}</textarea>
                <x-input-error :messages="$errors->get('visiting_hours')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3 mt-8">
                <x-primary-button>Update Profile</x-primary-button>
                <a href="{{ route('doctor.profile') }}">
                    <x-secondary-button type="button">Cancel</x-secondary-button>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
