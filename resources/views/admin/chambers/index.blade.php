@extends('admin.layouts.app')
@section('title', 'Chamber Management')
@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-white/90">Chamber Management</h1>
            <p class="mt-1 text-sm text-white/50">Manage Smart Serial chambers. Each chamber has an independent queue.</p>
        </div>
        <a href="{{ route('admin.chambers.create') }}" class="btn-gradient">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Chamber
        </a>
    </div>
</div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))
    <div class="mb-4 alert-glass">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif
    <div class="glass-card-static overflow-hidden">
        <div class="p-4 border-b border-white/5">
            <h3 class="text-sm font-medium text-white/70">All Chambers ({{ $chambers->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Chamber</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Prefix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Start #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white/40 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($chambers as $chamber)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white/90">{{ $chamber->name }}</p>
                                    @if($chamber->description)
                                        <p class="text-xs text-white/40 max-w-xs truncate">{{ $chamber->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-white/70">{{ $chamber->doctor->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4"><code class="text-xs text-white/50 bg-white/5 px-2 py-1 rounded">{{ $chamber->chamber_number ?? '—' }}</code></td>
                        <td class="px-6 py-4"><code class="text-xs text-cyan-400 bg-cyan-500/10 px-2 py-1 rounded">{{ $chamber->serial_prefix ?: '—' }}</code></td>
                        <td class="px-6 py-4 text-sm text-white/70">{{ $chamber->daily_starting_number }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $chamber->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                {{ $chamber->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.chambers.edit', $chamber) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-colors">Edit</a>
                                <form action="{{ route('admin.chambers.destroy', $chamber) }}" method="POST" onsubmit="return confirm('Delete this chamber?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-white/40">No chambers found. Create one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
