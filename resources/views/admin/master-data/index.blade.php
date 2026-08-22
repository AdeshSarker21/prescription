@extends('admin.layouts.app')

@section('title', $config['label'] . ' - Master Data')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">{{ $config['label'] }}</h1>
            <p class="text-sm text-white/50 mt-1">Manage {{ strtolower($config['label']) }} records used in prescriptions</p>
        </div>
        <a href="{{ route('admin.master-data.create', $module) }}" class="btn-gradient inline-flex items-center justify-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add {{ rtrim($config['label'], 's') }}
        </a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    <div class="glass-card-static p-4 mb-6">
        <form method="GET" action="{{ route('admin.master-data.index', $module) }}">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search {{ strtolower($config['label']) }}..." class="w-full glass-input">
                </div>
                <div class="w-full sm:w-40">
                    <select name="status" class="w-full glass-input">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn-outline-glass px-4 py-2 text-sm inline-flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.master-data.index', $module) }}" class="btn-outline-glass px-4 py-2 text-sm inline-flex items-center justify-center">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Name</th>
                        @if(!empty($config['detailsField']))
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Details</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Used</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        @php
                            $nameField = $config['nameField'];
                            $detailsField = $config['detailsField'] ?? null;
                        @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3 text-white/40">{{ $items->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-white/90 font-medium">{{ $item->$nameField }}</td>
                            @if($detailsField)
                            <td class="px-4 py-3 text-white/60 text-sm max-w-xs truncate">{{ $item->$detailsField ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.master-data.toggle-status', [$module, $item->id]) }}" class="inline">
                                    @csrf
                                    @if($item->status === 'active')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors cursor-pointer">Active</button>
                                    @else
                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors cursor-pointer">Inactive</button>
                                    @endif
                                </form>
                            </td>
                            <td class="px-4 py-3 text-white/50">{{ $item->used_count ?? 0 }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.master-data.edit', [$module, $item->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.master-data.destroy', [$module, $item->id]) }}" class="inline" data-confirm="Delete {{ $item->$nameField }}? This cannot be undone." data-title="Delete Record" data-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg hover:bg-red-500/20 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($config['detailsField']) ? 6 : 5 }}" class="text-center py-12 text-white/40">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p>No records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $items->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
