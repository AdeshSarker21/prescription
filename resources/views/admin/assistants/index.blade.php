@extends('admin.layouts.app')

@section('title', 'Assistant Management')
@section('header', 'Assistant Management')

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'assistants') }}' }">
    {{-- Tabs --}}
    <div class="flex items-center gap-1 p-1 glass-card-static w-fit">
        <button @click="tab = 'assistants'" :class="tab === 'assistants' ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
            Assistants List
        </button>
        <button @click="tab = 'assignments'" :class="tab === 'assignments' ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">
            Doctor Assignments
        </button>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    {{-- Assistants List Tab --}}
    <div x-show="tab === 'assistants'" x-cloak>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-white/90">All Assistants</h3>
                <p class="text-sm text-white/50 mt-1">Manage assistant accounts.</p>
            </div>
            <a href="{{ route('admin.assistants.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm font-semibold hover:bg-emerald-500/20 border border-emerald-500/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Assistant
            </a>
        </div>

        <div class="glass-table">
            <table>
                <thead>
                    <tr>
                        <th>Assistant</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assistants as $assistant)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-emerald-200">
                                    {{ substr($assistant->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-white/90">{{ $assistant->name }}</p>
                                    <p class="text-xs text-white/50">{{ $assistant->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-white/60">{{ $assistant->phone ?? '-' }}</td>
                        <td>
                            @if($assistant->status === 'active')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">{{ ucfirst($assistant->status) }}</span>
                            @endif
                        </td>
                        <td class="text-sm text-white/60">
                            @php
                                $assignedDoctors = \App\Models\DoctorAssistant::where('assistant_id', $assistant->id)
                                    ->pluck('doctor_id')
                                    ->map(fn($id) => \App\Models\User::find($id)?->name)
                                    ->filter()
                                    ->implode(', ');
                            @endphp
                            {{ $assignedDoctors ?: '-' }}
                        </td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('admin.assistants.edit', $assistant) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.assistants.destroy', $assistant) }}" class="inline" onsubmit="return confirm('Delete this assistant?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg hover:bg-red-500/20 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-white/40">
                            <svg class="w-12 h-12 mx-auto mb-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm">No assistants found. Create one first.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assistants->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $assistants->links() }}
            </div>
        @endif
    </div>

    {{-- Doctor Assignments Tab --}}
    <div x-show="tab === 'assignments'" x-cloak>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-white/90">Doctor-Assistant Assignments</h3>
                <p class="text-sm text-white/50 mt-1">Manage which assistants can book appointments for which doctors.</p>
            </div>
        </div>

        <div class="glass-table">
            <table>
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Assigned Assistants</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-indigo-200">
                                    {{ substr($doctor->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-white/90">{{ $doctor->name }}</p>
                                    <p class="text-xs text-white/50">{{ $doctor->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-white/60">{{ $doctor->specialization ?? '-' }}</td>
                        <td>
                            @if($doctor->assistants->isEmpty())
                                <span class="text-xs text-white/40 italic">No assistants assigned</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($doctor->assistants as $assistant)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20">
                                        {{ $assistant->name }}
                                        <form method="POST" action="{{ route('admin.assistants.remove-assignment', [$doctor->id, $assistant->id]) }}" class="inline" onsubmit="return confirm('Unassign this assistant?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ml-1 text-emerald-400 hover:text-emerald-300">&times;</button>
                                        </form>
                                    </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.assistants.assign', $doctor) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 text-xs font-semibold hover:bg-indigo-500/20 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Assign
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-12 text-white/40">
                            <svg class="w-12 h-12 mx-auto mb-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <p class="text-sm">No doctors found. Register doctors first.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctors->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
