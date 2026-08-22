@extends('admin.layouts.app')

@section('title', 'Medicine Suggestions - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Medicine Suggestions</h1>
            <p class="text-sm text-white/50 mt-1">Review doctor-submitted missing medicines.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-glass mb-6">{{ session('success') }}</div>
    @endif

    {{-- Status Tabs --}}
    @php
        $currentStatus = request('status');
        $pendingCount = \App\Models\MedicineSuggestion::where('status', 'pending')->count();
        $approvedCount = \App\Models\MedicineSuggestion::where('status', 'approved')->count();
        $rejectedCount = \App\Models\MedicineSuggestion::where('status', 'rejected')->count();
        $totalCount = \App\Models\MedicineSuggestion::count();
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.medicine-suggestions.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ !$currentStatus ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80 border border-white/5' }}">
            All <span class="ml-1.5 text-xs opacity-70">({{ $totalCount }})</span>
        </a>
        <a href="{{ route('admin.medicine-suggestions.index', ['status' => 'pending']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $currentStatus === 'pending' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80 border border-white/5' }}">
            Pending <span class="ml-1.5 text-xs opacity-70">({{ $pendingCount }})</span>
        </a>
        <a href="{{ route('admin.medicine-suggestions.index', ['status' => 'approved']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $currentStatus === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80 border border-white/5' }}">
            Approved <span class="ml-1.5 text-xs opacity-70">({{ $approvedCount }})</span>
        </a>
        <a href="{{ route('admin.medicine-suggestions.index', ['status' => 'rejected']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $currentStatus === 'rejected' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80 border border-white/5' }}">
            Rejected <span class="ml-1.5 text-xs opacity-70">({{ $rejectedCount }})</span>
        </a>
    </div>

    {{-- Table --}}
    <div class="glass-card-static">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white/90">Suggestions</h3>
            <form method="GET" action="{{ route('admin.medicine-suggestions.index') }}" class="flex gap-2">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="glass-input w-56">
                </div>
                <button type="submit" class="btn-outline-glass text-xs">Search</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Medicine Name</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Generic</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Strength</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Category</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Doctor</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Status</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Date</th>
                        <th class="text-right px-5 py-3 font-medium text-white/50 uppercase text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($suggestions as $s)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-white/90">{{ $s->name }}</div>
                                @if($s->company_name)
                                    <div class="text-xs text-white/40 mt-0.5">{{ $s->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-white/60">{{ $s->generic_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-white/60">{{ $s->strength ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @if($s->category)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-indigo-500/20 text-indigo-400 rounded-full">{{ $s->category->name }}</span>
                                @else
                                    <span class="text-white/40">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-white/70">{{ $s->doctor->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="status-badge
                                    {{ $s->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                    {{ $s->status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                    {{ $s->status === 'rejected' ? 'bg-red-500/20 text-red-400' : '' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-white/50 text-xs">{{ $s->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($s->status === 'pending')
                                        <form action="{{ route('admin.medicine-suggestions.approve', $s) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-lg border border-emerald-500/20 hover:bg-emerald-500/20 transition-all" title="Approve & Add to Medicines">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.medicine-suggestions.reject', $s) }}" method="POST" class="inline"
                                            data-confirm="Reject this suggestion?" data-title="Reject" data-confirm-text="Yes, reject" data-cancel-text="Cancel" data-icon="error">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-500/10 text-red-400 text-xs font-medium rounded-lg border border-red-500/20 hover:bg-red-500/20 transition-all" title="Reject">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.medicine-suggestions.edit', $s) }}" class="p-1.5 text-white/40 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.medicine-suggestions.destroy', $s) }}" method="POST" class="inline"
                                        data-confirm="Delete this suggestion permanently?" data-title="Delete" data-confirm-text="Yes, delete" data-cancel-text="Cancel" data-icon="error">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-white/40 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-white/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-white/50 font-medium">No suggestions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suggestions->hasPages())
            <div class="px-5 py-4 border-t border-white/5">
                {{ $suggestions->links() }}
            </div>
        @endif
    </div>
@endsection
