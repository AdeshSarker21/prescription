@extends('doctor.layouts.app')

@section('title', $medicine->name)

@section('header', $medicine->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('doctor.medicines.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Medicines
        </a>
        <a href="{{ route('doctor.medicines.suggest') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            Suggest Medicine
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Basic Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Name</dt>
                    <dd class="text-sm text-gray-900 font-medium">{{ $medicine->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Generic Name</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->generic_name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Brand Name</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->brand_name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Strength</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->strength ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Category</dt>
                    <dd class="text-sm">
                        @if($medicine->category)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $medicine->category }}</span>
                        @else
                        <span class="text-gray-500">N/A</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Composition & Manufacturer --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Composition & Manufacturer</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Composition</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->composition ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Company Name</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->company_name ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Dosage Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dosage Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Adult Dose</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->adult_dose ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Child Dose</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->child_dose ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Max Daily Dose</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->max_daily_dose ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Safety --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Safety Information</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Side Effects</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->side_effects ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Contraindications</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->contraindications ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Pregnancy Safe</dt>
                    <dd class="text-sm">
                        @if($medicine->pregnancy_safe === null)
                        <span class="text-gray-500">Unknown</span>
                        @elseif($medicine->pregnancy_safe)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Yes</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">No</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Allergy Warning</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->allergy_warning ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Alcohol Warning</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->alcohol_warning ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Usage --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Usage Instructions</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->usage_instructions ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase font-medium">Food Interaction</dt>
                    <dd class="text-sm text-gray-700">{{ $medicine->food_interaction ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
