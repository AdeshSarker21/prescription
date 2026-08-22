@extends('admin.layouts.app')

@section('title', __('Doctors - Admin'))

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">{{ __('Doctors') }}</h1>
            <p class="text-sm text-white/50 mt-1">{{ __('Manage all registered doctors in the system.') }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.doctors.create') }}" class="btn-gradient">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Doctor') }}
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="glass-card-static p-5">
            <p class="text-sm font-medium text-white/50">{{ __('Total Doctors') }}</p>
            <p class="text-2xl font-bold text-white/90 mt-1">{{ $doctors->count() }}</p>
        </div>
        <div class="glass-card-static p-5">
            <p class="text-sm font-medium text-white/50">{{ __('Active Today') }}</p>
            <p class="text-2xl font-bold text-white/90 mt-1">{{ $doctors->count() }}</p>
        </div>
        <div class="glass-card-static p-5">
            <p class="text-sm font-medium text-white/50">{{ __('Total Patients') }}</p>
            <p class="text-2xl font-bold text-white/90 mt-1">{{ $doctors->sum('patient_count') }}</p>
        </div>
        <div class="glass-card-static p-5">
            <p class="text-sm font-medium text-white/50">{{ __('Avg. Patients/Doctor') }}</p>
            <p class="text-2xl font-bold text-white/90 mt-1">{{ $doctors->count() > 0 ? round($doctors->sum('patient_count') / $doctors->count()) : 0 }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-glass mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- Doctors Table --}}
    <div class="glass-card-static" x-data="{ search: '' }">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white/90">{{ __('All Doctors') }}</h3>
            <div class="flex gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" placeholder="{{ __('Search doctors...') }}" class="glass-input w-64">
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Doctor') }}</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Email') }}</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Patients') }}</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Joined') }}</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Status') }}</th>
                        <th class="text-right px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($doctors as $doctor)
                        <tr x-show="search === '' || '{{ strtolower($doctor->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($doctor->email) }}'.includes(search.toLowerCase())" x-transition class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-500/20 rounded-full flex items-center justify-center text-sm font-semibold text-indigo-400">
                                        {{ strtoupper(substr($doctor->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white/90">{{ $doctor->locale('name') }}</p>
                                        @if ($doctor->tenant)
                                            <p class="text-xs text-white/50">{{ $doctor->tenant->name ?? 'N/A' }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-white/70">{{ $doctor->email }}</td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-white/90">{{ $doctor->patient_count }}</span>
                            </td>
                            <td class="px-5 py-4 text-white/70">{{ $doctor->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                    <span class="status-badge bg-emerald-500/20 text-emerald-400">
                                        {{ __('Active') }}
                                    </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.doctors.show', $doctor) }}" class="p-1.5 text-white/40 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all duration-200" title="{{ __('View Details') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="p-1.5 text-white/40 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all duration-200" title="{{ __('Edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST"
                                        data-confirm="{{ __('Delete :name? This action cannot be undone.', ['name' => $doctor->name]) }}"
                                        data-title="{{ __('Delete Doctor') }}"
                                        data-confirm-text="{{ __('Yes, delete') }}"
                                        data-cancel-text="{{ __('Keep it') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-white/40 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr x-show="search === ''">
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-white/30 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <p class="text-white/50 font-medium">{{ __('No doctors found') }}</p>
                                <p class="text-white/40 text-sm mt-1">{{ __('Doctors will appear here once they register.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
