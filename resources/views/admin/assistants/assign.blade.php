@extends('admin.layouts.app')

@section('title', 'Assign Assistants - ' . $doctor->name)
@section('header', 'Assign Assistants')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Assign Assistants to Dr. {{ $doctor->name }}</h3>
            <p class="text-sm text-gray-500 mt-1">Select assistants who can manage appointments for this doctor.</p>
        </div>
        <a href="{{ route('admin.assistants.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('admin.assistants.store-assignment', $doctor) }}">
        @csrf
        <div class="dashboard-card">
            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Select Assistants</h4>

            @if($assistants->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <p class="text-sm">No assistants registered yet. Create assistant accounts first.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($assistants as $assistant)
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-white/40 hover:bg-white/60 transition-all border border-white/30 cursor-pointer {{ in_array($assistant->id, $assignedIds) ? 'ring-2 ring-emerald-400 bg-emerald-50/30' : '' }}">
                        <input type="checkbox" name="assistant_ids[]" value="{{ $assistant->id }}"
                               {{ in_array($assistant->id, $assignedIds) ? 'checked' : '' }}
                               class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($assistant->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ $assistant->name }}</p>
                            <p class="text-xs text-gray-500">{{ $assistant->email }}</p>
                        </div>
                        @if(in_array($assistant->id, $assignedIds))
                            <span class="text-xs text-emerald-600 font-medium">Already assigned</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            @endif

            @error('assistant_ids')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-white/20">
                <button type="submit" class="btn-gradient">Save Assignments</button>
                <a href="{{ route('admin.assistants.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-white/30 transition-colors">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
