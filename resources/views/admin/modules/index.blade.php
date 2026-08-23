@extends('admin.layouts.app')

@section('title', 'Modules & Permissions')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Modules & Permissions</h1>
            <p class="text-sm text-white/50 mt-1">Manage modules, control access, and assign permissions</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-4">
            <p class="text-sm text-white/50">Total Modules</p>
            <p class="text-2xl font-bold text-white/90 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-sm text-white/50">Core (Always On)</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['core'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-sm text-white/50">Optional</p>
            <p class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['optional'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-sm text-white/50">Enabled</p>
            <p class="text-2xl font-bold text-indigo-400 mt-1">{{ $stats['enabled'] }}</p>
        </div>
    </div>

    {{-- Modules Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white/90">Registered Modules</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider">Global Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Plan Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Doctors Enabled</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white/40 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($modules as $key => $module)
                        @php
                            $doctorCount = 0;
                            foreach ($doctorsWithModules as $dwm) {
                                if (in_array($key, $dwm['modules'])) {
                                    $doctorCount++;
                                }
                            }
                            $dbModule = $dbModules->get($key);
                            $globalEnabled = $module['enabled'] ?? true;
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! module_icon($module['sidebar']['doctor']['icon'] ?? $module['sidebar']['admin']['icon'] ?? 'cog') !!}
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white/90">{{ $module['name'] }}</p>
                                        <p class="text-xs text-white/40">v{{ $module['version'] ?? '1.0.0' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-white/60 max-w-xs">{{ $module['description'] ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($module['core'] ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Core
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Optional
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($module['core'] ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Always On
                                    </span>
                                @else
                                    <form action="{{ route('admin.modules.toggle-global', $key) }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="enabled" value="{{ $globalEnabled ? '0' : '1' }}">
                                        <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $globalEnabled ? 'bg-indigo-500' : 'bg-white/10' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $globalEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs text-white/50 bg-white/5 px-2 py-1 rounded">{{ $module['plan_key'] ?? $key }}</code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-white/70">{{ $doctorCount }} / {{ $doctorsWithModules->count() }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.modules.permissions.index', $key) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-400 hover:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 rounded-lg transition-colors" title="Manage Permissions">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Permissions
                                    </a>
                                    <a href="{{ route('admin.modules.doctors', $key) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-colors" title="Doctor Settings">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Doctors
                                    </a>
                                    <a href="{{ route('admin.modules.users', $key) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 rounded-lg transition-colors" title="Assign Users">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Users
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Module Status Summary --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-white/70 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.doctor-feature-settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Doctor Feature Settings
            </a>
        </div>
    </div>
</div>
@endsection
