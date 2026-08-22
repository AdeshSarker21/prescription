@extends('admin.layouts.app')

@section('title', $medicine->name . ' - Admin')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.medicines.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Medicines
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white/90">{{ $medicine->name }}</h1>
                <p class="text-sm text-white/50 mt-1">{{ $medicine->generic_name ?? $medicine->brand_name ?? 'Medicine Details' }}</p>
            </div>
            <a href="{{ route('admin.medicines.edit', $medicine) }}" class="btn-gradient">
                Edit Medicine
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sidebar Info --}}
        <div class="space-y-6">
            <div class="glass-card-static p-6">
                <h3 class="text-sm font-semibold text-white/90 mb-3">Classification</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-white/50">Status</span><span class="font-medium px-2 py-0.5 text-xs rounded-full {{ $medicine->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">{{ ucfirst($medicine->status) }}</span></div>
                    <div class="flex justify-between"><span class="text-white/50">Category</span><span class="font-medium text-white/90">{{ $medicine->category->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-white/50">Strength</span><span class="font-medium text-white/90">{{ $medicine->strength ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-white/50">Pregnancy Safe</span><span class="font-medium text-white/90">{{ $medicine->pregnancy_safe ? 'Yes' : 'No' }}</span></div>
                    <div class="flex justify-between"><span class="text-white/50">Alcohol Warning</span><span class="font-medium text-white/90">{{ $medicine->alcohol_warning ? 'Yes' : 'No' }}</span></div>
                    <div class="flex justify-between"><span class="text-white/50">Batch Required</span><span class="font-medium text-white/90">{{ $medicine->batch_required ? 'Yes' : 'No' }}</span></div>
                </dl>
            </div>
            <div class="glass-card-static p-6">
                <h3 class="text-sm font-semibold text-white/90 mb-3">Manufacturer</h3>
                <dl class="space-y-2 text-sm">
                    <div><span class="text-white/50">Company</span><p class="font-medium text-white/90">{{ $medicine->company_name ?? '—' }}</p></div>
                    <div><span class="text-white/50">Country</span><p class="font-medium text-white/90">{{ $medicine->country ?? '—' }}</p></div>
                </dl>
            </div>
        </div>

        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">Composition</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="sm:col-span-2"><span class="text-white/50">Active Ingredients</span><p class="font-medium mt-1 text-white/90">{{ $medicine->active_ingredients ?? '—' }}</p></div>
                    <div class="sm:col-span-2"><span class="text-white/50">Salt Composition</span><p class="font-medium mt-1 text-white/90">{{ $medicine->salt_composition ?? '—' }}</p></div>
                </dl>
            </div>

            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">Dosage</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-white/50">Adult Dose</span><p class="font-medium mt-1 text-white/90">{{ $medicine->adult_dose ?? '—' }}</p></div>
                    <div><span class="text-white/50">Child Dose</span><p class="font-medium mt-1 text-white/90">{{ $medicine->child_dose ?? '—' }}</p></div>
                    <div><span class="text-white/50">Max Daily Dose</span><p class="font-medium mt-1 text-white/90">{{ $medicine->max_daily_dose ?? '—' }}</p></div>
                    <div><span class="text-white/50">Food Interaction</span><p class="font-medium mt-1 text-white/90">{{ $medicine->food_interaction ? str_replace('_', ' ', ucfirst($medicine->food_interaction)) : '—' }}</p></div>
                    <div class="sm:col-span-2"><span class="text-white/50">Duration</span><p class="font-medium mt-1 text-white/90">{{ $medicine->duration_recommendation ?? '—' }}</p></div>
                </dl>
            </div>

            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">Warnings & Safety</h3>
                <dl class="space-y-4 text-sm">
                    <div><span class="text-white/50">Side Effects</span><p class="font-medium mt-1 text-white/90">{{ $medicine->side_effects ?? '—' }}</p></div>
                    <div><span class="text-white/50">Contraindications</span><p class="font-medium mt-1 text-white/90">{{ $medicine->contraindications ?? '—' }}</p></div>
                    <div><span class="text-white/50">Allergy Warning</span><p class="font-medium mt-1 text-white/90">{{ $medicine->allergy_warning ?? '—' }}</p></div>
                </dl>
            </div>

            <div class="glass-card-static p-6">
                <h3 class="text-lg font-semibold text-white/90 mb-4">Smart Features</h3>
                <dl class="space-y-4 text-sm">
                    <div><span class="text-white/50">Drug Interaction Notes</span><p class="font-medium mt-1 text-white/90">{{ $medicine->drug_interaction_notes ?? '—' }}</p></div>
                    <div><span class="text-white/50">Usage Instructions</span><p class="font-medium mt-1 text-white/90">{{ $medicine->usage_instructions ?? '—' }}</p></div>
                </dl>
            </div>
        </div>
    </div>
@endsection
