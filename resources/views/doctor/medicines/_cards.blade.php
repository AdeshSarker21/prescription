@forelse($medicines as $medicine)
<a href="{{ route('doctor.medicines.show', $medicine) }}"
   class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-indigo-300 transition-all group">
    <div class="flex items-start justify-between mb-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-200 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        @if($medicine->category)
        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ $medicine->category->name ?? $medicine->category }}</span>
        @endif
    </div>
    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $medicine->name }}</h3>
    @if($medicine->generic_name)
    <p class="text-xs text-gray-500 mt-1">{{ $medicine->generic_name }}</p>
    @endif
    @if($medicine->strength)
    <p class="text-xs text-gray-400 mt-1"><span class="font-medium">Strength:</span> {{ $medicine->strength }}</p>
    @endif
    @if($medicine->company_name)
    <p class="text-xs text-gray-400 mt-1">{{ $medicine->company_name }}</p>
    @endif
</a>
@empty
<div class="col-span-full text-center py-12">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
    </svg>
    <p class="text-gray-500">No medicines found.</p>
</div>
@endforelse
