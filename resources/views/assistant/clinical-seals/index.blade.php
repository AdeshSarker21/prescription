@extends('assistant.layouts.app')

@section('title', 'Clinical Seals')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Clinical Seals</h1>
            <p class="text-sm text-gray-500 mt-1">Manage clinical seals for your assigned doctors</p>
        </div>
        <a href="{{ route('assistant.clinical-seals.create') }}" class="btn-gradient inline-flex items-center justify-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Seal
        </a>
    </div>

    <div class="dashboard-card mb-6">
        <form method="GET" action="{{ route('assistant.clinical-seals.index') }}">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search seals..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition">
                </div>
                <div class="w-full sm:w-40">
                    <select name="doctor_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-36">
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white/60 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 outline-none transition">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition inline-flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                @if(request('search') || request('status') || request('doctor_id'))
                    <a href="{{ route('assistant.clinical-seals.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-100 transition inline-flex items-center justify-center">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="dashboard-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Seal Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Details</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Doctor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Used</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seals as $index => $seal)
                        <tr class="border-b border-gray-50 hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $seals->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-gray-800 font-semibold">{{ $seal->name }}</td>
                            <td class="px-4 py-3 text-gray-500 text-sm max-w-xs truncate">{{ $seal->details ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $seal->doctor->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('assistant.clinical-seals.toggle-status', $seal->id) }}" class="inline">
                                    @csrf
                                    @if($seal->status === 'active')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer">Active</button>
                                    @else
                                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-500 border border-red-200 hover:bg-red-100 transition-colors cursor-pointer">Inactive</button>
                                    @endif
                                </form>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $seal->used_count ?? 0 }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('assistant.clinical-seals.edit', $seal->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                @if($seal->used_count == 0)
                                <form method="POST" action="{{ route('assistant.clinical-seals.destroy', $seal->id) }}" class="inline" data-confirm="Delete {{ $seal->name }}? This cannot be undone." data-title="Delete Seal" data-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-500 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p>No clinical seals found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($seals->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $seals->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
