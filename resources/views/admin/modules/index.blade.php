@extends('admin.layouts.app')

@section('title', 'Module Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Module Management</h1>
            <p class="text-sm text-white/50 mt-1">Manage optional modules and control per-doctor access</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Status</th>
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
                            <td class="px-6 py-4">
                                @if($module['enabled'] ?? true)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                        Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                        Disabled
                                    </span>
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
                                    <a href="{{ route('admin.modules.permissions.index', $key) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-400 hover:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Permissions
                                    </a>
                                    <a href="{{ route('admin.modules.doctors', $key) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Settings
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
