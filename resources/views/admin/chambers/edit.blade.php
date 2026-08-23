@extends('admin.layouts.app')
@section('title', 'Edit Chamber')
@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-white/90">Edit Chamber</h1>
        <p class="mt-1 text-sm text-white/50">Update chamber settings for {{ $chamber->name }}.</p>
    </div>
</div>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <form action="{{ route('admin.chambers.update', $chamber) }}" method="POST" class="glass-card-static p-6 space-y-6">
        @csrf @method('PATCH')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-white/70">Chamber Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $chamber->name) }}" required class="mt-1 block w-full glass-input">
                @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="chamber_number" class="block text-sm font-medium text-white/70">Chamber Number</label>
                <input type="text" name="chamber_number" id="chamber_number" value="{{ old('chamber_number', $chamber->chamber_number) }}" class="mt-1 block w-full glass-input">
                @error('chamber_number') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label for="doctor_id" class="block text-sm font-medium text-white/70">Doctor <span class="text-red-400">*</span></label>
            <select name="doctor_id" id="doctor_id" required class="mt-1 block w-full glass-input">
                <option value="">Select a doctor</option>
                @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $chamber->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                @endforeach
            </select>
            @error('doctor_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="serial_prefix" class="block text-sm font-medium text-white/70">Serial Prefix</label>
                <input type="text" name="serial_prefix" id="serial_prefix" value="{{ old('serial_prefix', $chamber->serial_prefix) }}" class="mt-1 block w-full glass-input" maxlength="20">
                <p class="mt-1 text-xs text-white/40">Optional. Prepended to serial numbers (e.g. A-001).</p>
                @error('serial_prefix') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="daily_starting_number" class="block text-sm font-medium text-white/70">Daily Starting Number <span class="text-red-400">*</span></label>
                <input type="number" name="daily_starting_number" id="daily_starting_number" value="{{ old('daily_starting_number', $chamber->daily_starting_number) }}" min="1" required class="mt-1 block w-full glass-input">
                @error('daily_starting_number') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-white/70">Description</label>
            <textarea name="description" id="description" rows="2" class="mt-1 block w-full glass-input">{{ old('description', $chamber->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $chamber->is_active) ? 'checked' : '' }} class="rounded border-white/10 bg-white/5 text-indigo-400 focus:ring-indigo-500" id="is_active">
            <label for="is_active" class="text-sm text-white/70">Active</label>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-gradient">Update Chamber</button>
            <a href="{{ route('admin.chambers.index') }}" class="btn-outline-glass">Cancel</a>
        </div>
    </form>
</div>
@endsection
