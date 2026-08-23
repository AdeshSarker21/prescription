@extends('admin.layouts.app')

@section('title', $module->name . ' - Permission Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('admin.modules.index') }}" class="text-white/40 hover:text-white/70 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-white/90">{{ $module->name }} Permissions</h1>
            </div>
            <p class="text-sm text-white/50 ml-8">Control granular access for each doctor</p>
        </div>
    </div>

    {{-- Permission Legend --}}
    <div class="glass-card rounded-xl p-4">
        <div class="flex flex-wrap items-center gap-4 text-xs text-white/50">
            <span class="font-medium text-white/70">Available Permissions:</span>
            @foreach($permissions as $perm)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    {{ ucfirst(str_replace('_', ' ', $perm->name)) }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- Doctors Permissions Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white/90">Doctor Permissions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider sticky left-0 bg-white/5 backdrop-blur-sm z-10" style="min-width: 200px;">Doctor</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider" style="min-width: 80px;">Module</th>
                        @foreach($permissions as $perm)
                            <th class="px-3 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider" style="min-width: 100px;">
                                <div class="flex flex-col items-center gap-1">
                                    <span>{{ ucfirst(str_replace('_', ' ', $perm->name)) }}</span>
                                </div>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider" style="min-width: 100px;">All</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($doctorsPermissions as $item)
                        @php
                            $allGranted = collect($item['permissions'])->every('is_granted', true);
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors" data-doctor-id="{{ $item['doctor']->id }}">
                            <td class="px-6 py-4 sticky left-0 bg-transparent z-10" style="min-width: 200px;">
                                <div class="flex items-center gap-3">
                                    @if($item['doctor']->avatar)
                                        <img src="{{ $item['doctor']->avatar_url }}" alt="{{ $item['doctor']->name }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ substr($item['doctor']->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-white/90 truncate">{{ $item['doctor']->name }}</p>
                                        <p class="text-xs text-white/40 truncate">{{ $item['doctor']->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4 text-center" style="min-width: 80px;">
                                @if($item['module_enabled'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        ON
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                        OFF
                                    </span>
                                @endif
                            </td>
                            @foreach($item['permissions'] as $perm)
                                <td class="px-3 py-4 text-center" style="min-width: 100px;">
                                    @if($item['module_enabled'])
                                        <form action="{{ route('admin.modules.permissions.update', [$moduleSlug, $item['doctor']->id]) }}" method="POST" class="inline-block permission-form">
                                            @csrf
                                            @method('PATCH')
                                            @if($perm['is_granted'])
                                                <input type="hidden" name="permissions[]" value="{{ $perm['name'] }}">
                                            @endif
                                            {{-- Collect all currently granted permissions --}}
                                            @php
                                                $currentGranted = collect($item['permissions'])->filter->is_granted->pluck('name')->toArray();
                                                if (!$perm['is_granted']) {
                                                    $currentGranted[] = $perm['name'];
                                                } else {
                                                    $currentGranted = array_diff($currentGranted, [$perm['name']]);
                                                }
                                            @endphp
                                            @foreach($currentGranted as $gp)
                                                @if($gp !== $perm['name'] || !$perm['is_granted'])
                                                    <input type="hidden" name="all_permissions[]" value="{{ $gp }}">
                                                @endif
                                            @endforeach
                                            <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $perm['is_granted'] ? 'bg-indigo-500' : 'bg-white/10' }}">
                                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform {{ $perm['is_granted'] ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-white/20">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-4 text-center" style="min-width: 100px;">
                                @if($item['module_enabled'])
                                    <form action="{{ route('admin.modules.permissions.toggle-all', [$moduleSlug, $item['doctor']->id]) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="enabled" value="{{ $allGranted ? '0' : '1' }}">
                                        <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $allGranted ? 'bg-emerald-500' : 'bg-white/10' }}">
                                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform {{ $allGranted ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-white/20">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($permissions) + 3 }}" class="px-6 py-12 text-center">
                                <p class="text-sm text-white/40">No doctors found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-white/70 mb-4">Bulk Actions</h3>
        <div class="flex flex-wrap gap-3">
            <button onclick="selectAllPermissions()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Grant All to All Doctors
            </button>
            <button onclick="revokeAllPermissions()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l2-2m-2 2l-2-2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Revoke All from All Doctors
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectAllPermissions() {
        if (!confirm('Grant ALL permissions to ALL doctors for this module?')) return;
        document.querySelectorAll('[data-doctor-id]').forEach(row => {
            const form = row.querySelector('form[action*="toggle-all"]');
            if (form) {
                const input = form.querySelector('input[name="enabled"]');
                if (input && input.value === '0') {
                    form.submit();
                }
            }
        });
    }

    function revokeAllPermissions() {
        if (!confirm('Revoke ALL permissions from ALL doctors for this module?')) return;
        document.querySelectorAll('[data-doctor-id]').forEach(row => {
            const form = row.querySelector('form[action*="toggle-all"]');
            if (form) {
                const input = form.querySelector('input[name="enabled"]');
                if (input && input.value === '1') {
                    form.submit();
                }
            }
        });
    }
</script>
@endpush
@endsection
