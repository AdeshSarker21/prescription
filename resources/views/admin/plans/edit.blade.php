@extends('admin.layouts.app')

@section('title', 'Edit Plan')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-white/90">Edit Plan</h1>
        <p class="mt-1 text-sm text-white/50">{{ $plan->name }}</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="glass-card-static p-6 space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-white/70">Plan Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required class="mt-1 block w-full glass-input">
                @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-white/70">Slug <span class="text-red-400">*</span></label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $plan->slug) }}" required class="mt-1 block w-full glass-input">
                @error('slug') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-white/70">Description</label>
            <textarea name="description" id="description" rows="2" class="mt-1 block w-full glass-input">{{ old('description', $plan->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <p class="block text-sm font-medium text-white/70 mb-3">Pricing by Billing Cycle <span class="text-red-400">*</span></p>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div>
                    <label for="monthly_price" class="block text-xs font-medium text-white/50">Monthly</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="monthly_price" id="monthly_price" value="{{ old('monthly_price', $plan->monthly_price) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('monthly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="quarterly_price" class="block text-xs font-medium text-white/50">3 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="quarterly_price" id="quarterly_price" value="{{ old('quarterly_price', $plan->quarterly_price) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('quarterly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="semi_annual_price" class="block text-xs font-medium text-white/50">6 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="semi_annual_price" id="semi_annual_price" value="{{ old('semi_annual_price', $plan->semi_annual_price) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('semi_annual_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="yearly_price" class="block text-xs font-medium text-white/50">12 Months</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="yearly_price" id="yearly_price" value="{{ old('yearly_price', $plan->yearly_price) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('yearly_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="lifetime_price" class="block text-xs font-medium text-white/50">Lifetime</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-white/50 text-sm">{{ config('app.currency', '$') }}</span>
                        <input style="padding-left:32px;" type="text" step="0.01" min="0" name="lifetime_price" id="lifetime_price" value="{{ old('lifetime_price', $plan->lifetime_price) }}" required class="pl-7 block w-full glass-input text-sm">
                    </div>
                    @error('lifetime_price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="max_patients" class="block text-sm font-medium text-white/70">Max Patients</label>
                <input type="number" min="0" name="max_patients" id="max_patients" value="{{ old('max_patients', $plan->max_patients) }}" placeholder="Leave empty for unlimited" class="mt-1 block w-full glass-input">
                @error('max_patients') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="sort_order" class="block text-sm font-medium text-white/70">Sort Order</label>
                <input type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="mt-1 block w-full glass-input">
                @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end gap-4 pb-1">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded border-white/10 bg-white/5 text-indigo-400 focus:ring-indigo-500">
                    <span class="text-sm text-white/70">Active</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }} class="rounded border-white/10 bg-white/5 text-indigo-400 focus:ring-indigo-500">
                    <span class="text-sm text-white/70">Popular</span>
                </label>
            </div>
        </div>

        <div>
            <label for="features" class="block text-sm font-medium text-white/70">Features <span class="text-white/40">(one per line)</span></label>
            <textarea name="features" id="features" rows="6" class="mt-1 block w-full glass-input font-mono text-sm">{{ old('features', is_array($plan->features) ? implode("\n", $plan->features) : $plan->features) }}</textarea>
            @error('features') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="limitations" class="block text-sm font-medium text-white/70">Limitations <span class="text-white/40">(one per line)</span></label>
            <textarea name="limitations" id="limitations" rows="3" class="mt-1 block w-full glass-input font-mono text-sm">{{ old('limitations', is_array($plan->limitations) ? implode("\n", $plan->limitations) : $plan->limitations) }}</textarea>
            @error('limitations') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-gradient">Update Plan</button>
            <a href="{{ route('admin.plans.index') }}" class="btn-outline-glass">Cancel</a>
        </div>
    </form>
</div>
@endsection
