@extends('admin.layouts.app')

@section('title', 'Create Add-on')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-white/90">Create Add-on</h1>
        <p class="mt-1 text-sm text-white/50">Add a new module add-on that doctors can purchase.</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <form action="{{ route('admin.addons.store') }}" method="POST" class="glass-card-static p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-white/70">Add-on Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full glass-input">
                @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-white/70">Slug <span class="text-red-400">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required class="mt-1 block w-full glass-input">
                @error('slug') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="module_id" class="block text-sm font-medium text-white/70">Linked Module <span class="text-red-400">*</span></label>
            <select name="module_id" id="module_id" required class="mt-1 block w-full glass-input">
                <option value="">Select a module</option>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                        {{ $module->name }} {{ $module->is_core ? '(Core)' : '(Optional)' }}
                    </option>
                @endforeach
            </select>
            @error('module_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-white/70">Description</label>
            <textarea name="description" id="description" rows="2" class="mt-1 block w-full glass-input">{{ old('description') }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <p class="block text-sm font-medium text-white/70 mb-3">Pricing by Billing Cycle <span class="text-red-400">*</span></p>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label for="monthly_price" class="block text-xs font-medium text-white/50">Monthly</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="monthly_price" id="monthly_price" value="{{ old('monthly_price', 0) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('monthly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="quarterly_price" class="block text-xs font-medium text-white/50">3 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="quarterly_price" id="quarterly_price" value="{{ old('quarterly_price', 0) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('quarterly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="semi_annual_price" class="block text-xs font-medium text-white/50">6 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="semi_annual_price" id="semi_annual_price" value="{{ old('semi_annual_price', 0) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('semi_annual_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="yearly_price" class="block text-xs font-medium text-white/50">12 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="yearly_price" id="yearly_price" value="{{ old('yearly_price', 0) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('yearly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="lifetime_price" class="block text-xs font-medium text-white/50">Lifetime</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="lifetime_price" id="lifetime_price" value="{{ old('lifetime_price', 0) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('lifetime_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="sort_order" class="block text-sm font-medium text-white/70">Sort Order</label>
                <input type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="mt-1 block w-full glass-input">
                @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-white/10 bg-white/5 text-indigo-400 focus:ring-indigo-500">
                    <span class="text-sm text-white/70">Active</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-gradient">Create Add-on</button>
            <a href="{{ route('admin.addons.index') }}" class="btn-outline-glass">Cancel</a>
        </div>
    </form>
</div>
@endsection
