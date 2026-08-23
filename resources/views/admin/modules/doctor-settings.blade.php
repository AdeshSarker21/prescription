@extends('admin.layouts.app')

@section('title', $module['name'] . ' - Doctor Settings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('admin.modules.index') }}" class="text-white/40 hover:text-white/70 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-white/90">{{ $module['name'] }}</h1>
            </div>
            <p class="text-sm text-white/50 ml-8">Enable or disable this module for individual doctors</p>
        </div>
    </div>

    {{-- Module Info --}}
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! module_icon($module['sidebar']['doctor']['icon'] ?? $module['sidebar']['admin']['icon'] ?? 'cog') !!}
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-white/90">{{ $module['name'] }}</h3>
                <p class="text-sm text-white/50 mt-1">{{ $module['description'] ?? '' }}</p>
                <div class="flex items-center gap-4 mt-3">
                    <span class="text-xs text-white/40">Plan Key: <code class="bg-white/5 px-1.5 py-0.5 rounded">{{ $module['plan_key'] ?? $moduleKey }}</code></span>
                    <span class="text-xs text-white/40">Type: {{ ($module['core'] ?? false) ? 'Core' : 'Optional' }}</span>
                    <span class="text-xs text-white/40">Version: {{ $module['version'] ?? '1.0.0' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Doctor Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white/90">Doctor Access</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">In Plan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider">Module Enabled</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white/40 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($doctors as $item)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item['doctor']->avatar)
                                        <img src="{{ $item['doctor']->avatar_url }}" alt="{{ $item['doctor']->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($item['doctor']->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-white/90">{{ $item['doctor']->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-white/50">{{ $item['doctor']->email }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php $plan = $item['doctor']->activePlan(); @endphp
                                @if($plan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        {{ $plan->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-white/30">No plan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item['in_plan'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Included
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Not in plan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item['in_plan'])
                                    <form action="{{ route('admin.modules.doctors.update', [$moduleKey, $item['doctor']->id]) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="enabled" value="{{ $item['enabled'] ? '0' : '1' }}">
                                        <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $item['enabled'] ? 'bg-indigo-500' : 'bg-white/10' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $item['enabled'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-white/20">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($item['in_plan'])
                                    <form action="{{ route('admin.modules.doctors.update', [$moduleKey, $item['doctor']->id]) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="enabled" value="{{ $item['enabled'] ? '0' : '1' }}">
                                        <button type="submit" class="text-xs {{ $item['enabled'] ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' }} transition-colors">
                                            {{ $item['enabled'] ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm text-white/40">No doctors found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
