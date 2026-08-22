@extends('doctor.layouts.app')

@section('title', 'Suggest Medicine')

@section('header', 'Suggest Medicine')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('doctor.medicines.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Medicines
        </a>
        <a href="{{ route('doctor.medicines.suggestions') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">My Suggestions</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Suggest a New Medicine</h3>
        <p class="text-sm text-gray-500 mb-6">Use this form to suggest adding a new medicine to the database. The admin will review your suggestion.</p>

        <form method="POST" action="{{ route('doctor.medicines.storeSuggestion') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" value="Medicine Name *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required placeholder="e.g. Paracetamol" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="generic_name" value="Generic Name" />
                    <x-text-input id="generic_name" name="generic_name" type="text" class="mt-1 block w-full" :value="old('generic_name')" placeholder="e.g. Acetaminophen" />
                    <x-input-error :messages="$errors->get('generic_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="strength" value="Strength" />
                    <x-text-input id="strength" name="strength" type="text" class="mt-1 block w-full" :value="old('strength')" placeholder="e.g. 500mg" />
                    <x-input-error :messages="$errors->get('strength')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="category" value="Category" />
                    <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(old('category') == $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="company_name" value="Company Name" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name')" placeholder="e.g. ABC Pharmaceuticals" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="reason" value="Reason for Suggestion *" />
                <textarea id="reason" name="reason" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Explain why this medicine should be added..." required>{{ old('reason') }}</textarea>
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>

            <div class="flex items-center gap-3 mt-8">
                <x-primary-button>Submit Suggestion</x-primary-button>
                <a href="{{ route('doctor.medicines.index') }}">
                    <x-secondary-button type="button">Cancel</x-secondary-button>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
