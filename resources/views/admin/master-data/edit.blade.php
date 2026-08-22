@extends('admin.layouts.app')

@section('title', 'Edit ' . rtrim($config['label'], 's') . ' - Master Data')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.master-data.index', $module) }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to {{ $config['label'] }}
        </a>
        <h1 class="text-2xl font-bold text-white/90">Edit {{ rtrim($config['label'], 's') }}</h1>
    </div>

    <div class="max-w-2xl glass-card-static p-6">
        <form method="POST" action="{{ route('admin.master-data.update', [$module, $item->id]) }}"
            data-confirm="Save changes to {{ $item->{$config['nameField']} }}?"
            data-title="Update {{ rtrim($config['label'], 's') }}"
            data-confirm-text="Yes, save"
            data-cancel-text="Cancel"
            data-icon="question"
            data-confirm-color="#4f46e5">
            @csrf
            @method('PATCH')

            <div class="mb-5">
                <label class="block text-sm font-medium text-white/70 mb-1">{{ ucwords(str_replace('_', ' ', $config['nameField'])) }} *</label>
                <input type="text" name="{{ $config['nameField'] }}" value="{{ old($config['nameField'], $item->{$config['nameField']}) }}" required
                    class="w-full glass-input" placeholder="Enter {{ strtolower(str_replace('_', ' ', $config['nameField'])) }} name">
                @error($config['nameField']) <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            @if(!empty($config['detailsField']))
            <div class="mb-5">
                <label class="block text-sm font-medium text-white/70 mb-1">{{ ucwords(str_replace('_', ' ', $config['detailsField'])) }}</label>
                <textarea name="{{ $config['detailsField'] }}" rows="3"
                    class="w-full glass-input" placeholder="Enter {{ strtolower(str_replace('_', ' ', $config['detailsField'])) }}">{{ old($config['detailsField'], $item->{$config['detailsField']}) }}</textarea>
                @error($config['detailsField']) <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-white/70 mb-1">Status</label>
                <select name="status" class="w-full glass-input">
                    <option value="active" {{ old('status', $item->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $item->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-white/5">
                <button type="submit" class="btn-gradient">Update {{ rtrim($config['label'], 's') }}</button>
                <a href="{{ route('admin.master-data.index', $module) }}" class="btn-outline-glass">Cancel</a>
            </div>
        </form>
    </div>
@endsection
