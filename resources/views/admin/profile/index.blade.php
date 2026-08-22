@extends('admin.layouts.app')

@section('title', 'My Profile')
@section('header', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ avatarPreview: null }">

    {{-- Success Message --}}
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Profile Information --}}
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="relative">
                <template x-if="!avatarPreview">
                    @if($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover shadow-lg">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-indigo-500/20">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </template>
                <img x-show="avatarPreview" :src="avatarPreview" class="w-16 h-16 rounded-2xl object-cover shadow-lg">
            </div>
            <div>
                <h3 class="text-lg font-bold text-white/90">{{ $user->name }}</h3>
                <p class="text-sm text-white/50">{{ $user->email }}</p>
                <p class="text-xs text-indigo-400 font-medium mt-0.5">Administrator</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('POST')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                    @error('name')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                    @error('phone')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="avatar" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Profile Photo</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden"
                        x-on:change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { avatarPreview = e.target.result; }; reader.readAsDataURL(file); }">
                    <label for="avatar" class="inline-flex items-center px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-semibold text-indigo-400 cursor-pointer hover:bg-white/10 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Choose Photo
                    </label>
                    <p class="text-xs text-white/30 mt-1">JPG, PNG or GIF. Max 2MB.</p>
                    @error('avatar')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
        <h3 class="text-base font-bold text-white/90 mb-4">Change Password</h3>

        <form method="POST" action="{{ route('admin.profile.changePassword') }}" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Current Password</label>
                <input type="password" id="current_password" name="current_password" required
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                @error('current_password')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">New Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                    @error('password')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/90 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all placeholder-white/30">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
