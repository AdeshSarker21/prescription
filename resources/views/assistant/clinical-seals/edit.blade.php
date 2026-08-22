@extends('assistant.layouts.app')

@section('title', 'Edit Clinical Seal')

@section('content')
    <div class="mb-6">
        <a href="{{ route('assistant.clinical-seals.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Clinical Seals
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Clinical Seal</h1>
    </div>

    <div class="max-w-2xl">
        <div class="dashboard-card">
            <form method="POST" action="{{ route('assistant.clinical-seals.update', $seal->id) }}">
                @csrf
                @method('PATCH')

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Doctor *</label>
                    <select name="doctor_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition">
                        <option value="">Select Doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id', $seal->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    @error('doctor_id') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Seal Name *</label>
                    <input type="text" name="name" value="{{ old('name', $seal->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition"
                        placeholder="Enter seal name (will appear bold)">
                    @error('name') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Seal Details</label>
                    <textarea name="details" rows="4"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition"
                        placeholder="Enter seal details (normal text, optional)">{{ old('details', $seal->details) }}</textarea>
                    @error('details') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-5 border-t border-gray-100">
                    <button type="submit" class="btn-gradient">Update Seal</button>
                    <a href="{{ route('assistant.clinical-seals.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
