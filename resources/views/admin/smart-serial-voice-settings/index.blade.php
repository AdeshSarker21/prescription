@extends('admin.layouts.app')

@section('title', 'Smart Serial Voice Settings')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Smart Serial Voice Settings</h1>
            <p class="text-sm text-white/50 mt-1">Configure Bengali TTS providers and voice settings for each doctor.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.smart-serial-voice.clear-cache') }}"
               onclick="return confirm('Clear all cached audio files?')"
               class="px-4 py-2 text-sm font-medium text-white/70 bg-white/5 hover:bg-white/10 rounded-lg transition-all">
                Clear Audio Cache
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Provider Info --}}
    <div class="glass-card rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white/90 mb-4">Available TTS Providers</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($providers as $key => $provider)
                <div class="p-4 rounded-lg bg-white/5 border border-white/5">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full {{ $provider['requires_key'] ? 'bg-amber-400' : 'bg-emerald-400' }}"></div>
                        <span class="text-sm font-medium text-white/90">{{ $provider['name'] }}</span>
                    </div>
                    <p class="text-xs text-white/50 mb-2">{{ $provider['description'] }}</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($provider['languages'] as $lang)
                            <span class="px-1.5 py-0.5 text-[10px] font-medium bg-white/5 rounded text-white/40">{{ $lang }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Doctors Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white/90">Doctor Voice Configuration</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white/50 uppercase tracking-wider">Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white/50 uppercase tracking-wider">Voice</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white/50 uppercase tracking-wider">Display</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white/50 uppercase tracking-wider">TTS Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white/50 uppercase tracking-wider">Language</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-white/50 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($doctors as $doctor)
                        @php $settings = $doctor->smartSerialSetting; @endphp
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($doctor->avatar)
                                        <img src="{{ $doctor->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($doctor->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-white/90">{{ $doctor->name }}</p>
                                        <p class="text-xs text-white/40">{{ $doctor->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($settings && $settings->voice_enabled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-white/5 text-white/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span> Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($settings && $settings->display_enabled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-white/5 text-white/40">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span> Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-white/70">
                                    {{ $providers[$settings->tts_provider ?? 'google_translate']['name'] ?? 'Google Translate' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-white/70">{{ $settings->tts_language ?? 'bn-BD' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.smart-serial-voice.edit', $doctor->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 rounded-lg transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Configure
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-white/40">
                                No doctors found. Doctors must be registered first.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
