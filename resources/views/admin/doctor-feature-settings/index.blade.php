@extends('admin.layouts.app')

@section('title', 'Doctor Feature Settings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Doctor Feature Settings</h1>
            <p class="text-sm text-white/50 mt-1">Enable or disable prescription features for each doctor</p>
        </div>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    @php
        $featureColumns = [
            'complaints' => 'Complaints',
            'tests' => 'Tests',
            'medical_history' => 'Past Medical History',
            'advice' => 'Advice',
            'clinical_features' => 'Clinical Features',
            'family_history' => 'Family History',
            'menstrual_history' => 'Menstrual History',
            'drug_history' => 'Drug History',
            'ot_note' => 'OT Note',
            'anesthesia' => 'Anesthesia',
            'procedure' => 'Procedure',
            'treatment_plan' => 'Treatment Plan',
        ];
    @endphp

    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                        @foreach($featureColumns as $label)
                            <th class="px-3 py-3 text-center text-xs font-medium text-white/50 uppercase w-24">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        @php $setting = $doctor->doctorFeatureSetting; @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="px-4 py-3 text-white/90 font-medium">{{ $doctor->name }}</td>
                            @foreach($featureColumns as $key => $label)
                                @php $enabled = $setting && (bool) $setting->{$key}; @endphp
                                <td class="px-3 py-3 text-center">
                                    <form method="POST"
                                          action="{{ route('admin.doctor-feature-settings.update', $doctor->id) }}"
                                          x-data="{ on: {{ $enabled ? 'true' : 'false' }} }">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="feature_key" value="{{ $key }}">
                                        <input type="hidden" name="value" value="0">
                                        <button type="button"
                                                @click="on = !on; const f = $el.closest('form'); f.querySelector('input[name=value]').value = on ? '1' : '0'; f.requestSubmit();"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none cursor-pointer"
                                                :class="on ? 'bg-indigo-500' : 'bg-white/15'"
                                                title="{{ $label }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                                :class="on ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                        </button>
                                    </form>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($featureColumns) + 1 }}" class="text-center py-12 text-white/40">No doctors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($doctors->hasPages())
        <div class="px-4 py-3 border-t border-white/5">
            {{ $doctors->links() }}
        </div>
        @endif
    </div>
@endsection