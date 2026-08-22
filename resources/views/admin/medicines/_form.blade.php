    <div class="mb-6">
        <a href="{{ route('admin.medicines.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Medicines
        </a>
        <h1 class="text-2xl font-bold text-white/90">{{ $title }}</h1>
    </div>

    <div class="max-w-4xl glass-card-static p-6">
        <form method="POST" action="{{ $route }}" enctype="multipart/form-data"
            data-confirm="{{ $medicine && $medicine->id ? 'Save changes to ' . $medicine->name . '?' : 'Save this medicine?' }}"
            data-title="{{ $title }}"
            data-confirm-text="{{ $medicine && $medicine->id ? 'Yes, save' : 'Yes, create' }}"
            data-cancel-text="Cancel"
            data-icon="question"
            data-confirm-color="#4f46e5">
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            {{-- Basic Info --}}
            <h3 class="text-lg font-semibold text-white/90 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Medicine Name *</label>
                    <input type="text" name="name" value="{{ old('name', $medicine->name ?? '') }}" required class="w-full glass-input">
                    @error('name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Generic Name</label>
                    <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Brand Name</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $medicine->brand_name ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Category</label>
                    <select name="category_id" class="w-full glass-input">
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $medicine->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Composition --}}
            <h3 class="text-lg font-semibold text-white/90 mt-8 mb-4">Composition Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Strength</label>
                    <input type="text" name="strength" value="{{ old('strength', $medicine->strength ?? '') }}" placeholder="e.g. 500mg" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Batch Required</label>
                    <select name="batch_required" class="w-full glass-input">
                        <option value="0" {{ old('batch_required', $medicine->batch_required ?? false) ? '' : 'selected' }}>No</option>
                        <option value="1" {{ old('batch_required', $medicine->batch_required ?? false) ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Active Ingredients</label>
                    <textarea name="active_ingredients" rows="2" class="w-full glass-input">{{ old('active_ingredients', $medicine->active_ingredients ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Salt Composition</label>
                    <textarea name="salt_composition" rows="2" class="w-full glass-input">{{ old('salt_composition', $medicine->salt_composition ?? '') }}</textarea>
                </div>
            </div>

            {{-- Manufacturer --}}
            <h3 class="text-lg font-semibold text-white/90 mt-8 mb-4">Manufacturer Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $medicine->company_name ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Country</label>
                    <input type="text" name="country" value="{{ old('country', $medicine->country ?? '') }}" class="w-full glass-input">
                </div>
            </div>

            {{-- Dosage --}}
            <h3 class="text-lg font-semibold text-white/90 mt-8 mb-4">Dosage Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Adult Dose</label>
                    <input type="text" name="adult_dose" value="{{ old('adult_dose', $medicine->adult_dose ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Child Dose</label>
                    <input type="text" name="child_dose" value="{{ old('child_dose', $medicine->child_dose ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Max Daily Dose</label>
                    <input type="text" name="max_daily_dose" value="{{ old('max_daily_dose', $medicine->max_daily_dose ?? '') }}" class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Food Interaction</label>
                    <select name="food_interaction" class="w-full glass-input">
                        <option value="">Select</option>
                        <option value="before_food" {{ old('food_interaction', $medicine->food_interaction ?? '') === 'before_food' ? 'selected' : '' }}>Before Food</option>
                        <option value="after_food" {{ old('food_interaction', $medicine->food_interaction ?? '') === 'after_food' ? 'selected' : '' }}>After Food</option>
                        <option value="with_food" {{ old('food_interaction', $medicine->food_interaction ?? '') === 'with_food' ? 'selected' : '' }}>With Food</option>
                        <option value="empty_stomach" {{ old('food_interaction', $medicine->food_interaction ?? '') === 'empty_stomach' ? 'selected' : '' }}>Empty Stomach</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Duration Recommendation</label>
                    <textarea name="duration_recommendation" rows="2" class="w-full glass-input">{{ old('duration_recommendation', $medicine->duration_recommendation ?? '') }}</textarea>
                </div>
            </div>

            {{-- Warnings --}}
            <h3 class="text-lg font-semibold text-white/90 mt-8 mb-4">Warnings & Safety</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Pregnancy Safe</label>
                    <select name="pregnancy_safe" class="w-full glass-input">
                        <option value="1" {{ old('pregnancy_safe', $medicine->pregnancy_safe ?? true) ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('pregnancy_safe', $medicine->pregnancy_safe ?? true) ? '' : 'selected' }}>No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Alcohol Warning</label>
                    <select name="alcohol_warning" class="w-full glass-input">
                        <option value="0" {{ old('alcohol_warning', $medicine->alcohol_warning ?? false) ? '' : 'selected' }}>No</option>
                        <option value="1" {{ old('alcohol_warning', $medicine->alcohol_warning ?? false) ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Side Effects</label>
                    <textarea name="side_effects" rows="2" class="w-full glass-input">{{ old('side_effects', $medicine->side_effects ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Contraindications</label>
                    <textarea name="contraindications" rows="2" class="w-full glass-input">{{ old('contraindications', $medicine->contraindications ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Allergy Warning</label>
                    <textarea name="allergy_warning" rows="2" class="w-full glass-input">{{ old('allergy_warning', $medicine->allergy_warning ?? '') }}</textarea>
                </div>
            </div>

            {{-- AI / Smart --}}
            <h3 class="text-lg font-semibold text-white/90 mt-8 mb-4">Smart Features</h3>
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Drug Interaction Notes</label>
                    <textarea name="drug_interaction_notes" rows="2" class="w-full glass-input">{{ old('drug_interaction_notes', $medicine->drug_interaction_notes ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Usage Instructions</label>
                    <textarea name="usage_instructions" rows="2" class="w-full glass-input">{{ old('usage_instructions', $medicine->usage_instructions ?? '') }}</textarea>
                </div>
            </div>

            {{-- Status --}}
            <div class="mt-6 pt-6 border-t border-white/5">
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-white/70 mb-1">Status</label>
                    <select name="status" class="w-full glass-input">
                        <option value="active" {{ old('status', $medicine->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ old('status', $medicine->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status', $medicine->status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-white/5">
                <button type="submit" class="btn-gradient">
                    {{ $medicine && $medicine->id ? 'Update Medicine' : 'Create Medicine' }}
                </button>
                <a href="{{ route('admin.medicines.index') }}" class="btn-outline-glass">Cancel</a>
            </div>
        </form>
    </div>
