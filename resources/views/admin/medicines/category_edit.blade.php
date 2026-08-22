@extends('admin.layouts.app')

@section('title', 'Edit Category - Admin')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.medicines.categories.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Categories
        </a>
        <h1 class="text-2xl font-bold text-white/90">Edit Category</h1>
    </div>

    <div class="max-w-lg glass-card-static p-6">
        <form method="POST" action="{{ route('admin.medicines.categories.update', $category) }}">
            @csrf @method('PATCH')
            <div class="mb-4">
                <label class="block text-sm font-medium text-white/70 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full glass-input">
                @error('name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-white/70 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full glass-input">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-white/70 mb-1">Active</label>
                <select name="is_active" class="w-full glass-input">
                    <option value="1" {{ $category->is_active ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$category->is_active ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 btn-gradient">Update</button>
        </form>
    </div>
@endsection
