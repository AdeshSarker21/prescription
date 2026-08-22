@extends('doctor.layouts.app')

@section('title', $patient->name . ' - History')

@section('header', $patient->name . ' - History')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
                {{ substr($patient->name, 0, 2) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $patient->name }}</h3>
                <p class="text-sm text-gray-500">Prescription History</p>
            </div>
        </div>
        <a href="{{ route('doctor.patients.show', $patient) }}" class="text-sm text-indigo-600 hover:text-indigo-900">Back to Patient</a>
    </div>

    <div class="relative">
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-indigo-200"></div>
        <div class="space-y-6">
            @forelse($patient->prescriptions as $prescription)
            <div class="relative pl-14">
                <div class="absolute left-4 top-1 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow"></div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ $prescription->created_at->format('F d, Y') }}</p>
                            <p class="text-sm font-semibold text-indigo-600">Prescription #{{ $prescription->prescription_number }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                            <a href="{{ route('doctor.prescriptions.print', $prescription) }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Print</a>
                        </div>
                    </div>

                    @if($prescription->diagnosis)
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Diagnosis</p>
                        <p class="text-sm text-gray-700">{{ $prescription->diagnosis }}</p>
                    </div>
                    @endif

                    @if($prescription->items && count($prescription->items) > 0)
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-medium mb-2">Medicines</p>
                        <div class="space-y-2">
                            @foreach($prescription->items as $item)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">{{ $loop->iteration }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $item->medicine_name }}</p>
                                    <p class="text-xs text-gray-500">@if($item->dosage && !str_contains($item->medicine_name, $item->dosage)){{ $item->dosage }} | @endif{{ $item->frequency }} | {{ $item->duration }}</p>
                                    @if($item->instructions)
                                    <p class="text-xs text-gray-400 mt-1">{{ $item->instructions }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($prescription->notes)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase font-medium mb-1">Notes</p>
                        <p class="text-sm text-gray-700">{{ $prescription->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500">No prescription history found for this patient.</p>
                <a href="{{ route('doctor.prescriptions.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium mt-2 inline-block">Create Prescription</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
