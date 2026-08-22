@extends('admin.layouts.app')

@section('title', 'Add Assistant')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.assistants.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Assistants
        </a>
        <h1 class="text-2xl font-bold text-white/90">Add Assistant</h1>
        <p class="text-sm text-white/50 mt-1">Create a new assistant account.</p>
    </div>

    <div class="max-w-3xl glass-card-static p-6">
        <form method="POST" action="{{ route('admin.assistants.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-white/70 mb-1">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="w-full glass-input" placeholder="Full Name">
                    @error('name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-white/70 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="w-full glass-input" placeholder="assistant@example.com">
                    @error('email') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white/70 mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                        class="w-full glass-input" placeholder="Min 8 characters">
                    @error('password') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-white/70 mb-1">Phone <span class="text-xs text-white/40">(optional)</span></label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full glass-input" placeholder="+8801700000000">
                    @error('phone') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn-gradient">Create Assistant</button>
                <a href="{{ route('admin.assistants.index') }}" class="px-4 py-2 text-sm font-medium text-white/60 hover:text-white/80 rounded-lg hover:bg-white/5 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
@endsection
