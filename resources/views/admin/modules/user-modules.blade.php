@extends('admin.layouts.app')

@section('title', $module['name'] . ' - User Assignment')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('admin.modules.index') }}" class="text-white/40 hover:text-white/70 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-white/90">{{ $module['name'] }} - User Assignment</h1>
            </div>
            <p class="text-sm text-white/50 ml-8">Assign or remove this module for individual users</p>
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
                    <span class="text-xs text-white/40">Enabled: {{ $enabledCount }} / {{ $users->count() }} users</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white/90">User Access</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white/40 uppercase tracking-wider">Module Enabled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white/40 uppercase tracking-wider">Last Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-white/40 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($users as $item)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item['user']->avatar)
                                        <img src="{{ $item['user']->avatar_url }}" alt="{{ $item['user']->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($item['user']->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-white/90">{{ $item['user']->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php $role = $item['user']->roles->first(); @endphp
                                @if($role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $role->name === 'admin' ? 'bg-red-500/10 text-red-400 border border-red-500/20' :
                                           ($role->name === 'doctor' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' :
                                           'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20') }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @else
                                    <span class="text-xs text-white/30">No role</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-white/50">{{ $item['user']->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.modules.users.toggle', [$moduleKey, $item['user']->id]) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="enabled" value="{{ $item['is_enabled'] ? '0' : '1' }}">
                                    <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $item['is_enabled'] ? 'bg-indigo-500' : 'bg-white/10' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $item['is_enabled'] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                @if($item['enabled_at'])
                                    <span class="text-xs text-white/40">{{ $item['enabled_at']->diffForHumans() }}</span>
                                @else
                                    <span class="text-xs text-white/20">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.modules.users.toggle', [$moduleKey, $item['user']->id]) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="enabled" value="{{ $item['is_enabled'] ? '0' : '1' }}">
                                    <button type="submit" class="text-xs {{ $item['is_enabled'] ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' }} transition-colors">
                                        {{ $item['is_enabled'] ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm text-white/40">No users found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
