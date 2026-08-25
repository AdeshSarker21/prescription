@extends('admin.layouts.app')

@section('title', 'Configure Voice - ' . $doctor->name)

@section('content')
<div class="max-w-4xl mx-auto" x-data="voiceSettings()">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.smart-serial-voice.index') }}" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/60 hover:text-white/90 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white/90">Voice Settings</h1>
            <p class="text-sm text-white/50">{{ $doctor->name }} — {{ $doctor->email }}</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.smart-serial-voice.update', $doctor->id) }}">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- General Settings --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white/90 mb-4">General</h2>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-white/80">Voice Announcements</label>
                            <p class="text-xs text-white/40">Enable Bengali voice announcements on Patient Display</p>
                        </div>
                        <button type="button" @click="voice_enabled = !voice_enabled"
                                :class="voice_enabled ? 'bg-indigo-500' : 'bg-white/10'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                            <span :class="voice_enabled ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                        <input type="hidden" name="voice_enabled" :value="voice_enabled ? 1 : 0">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-white/80">Patient Display</label>
                            <p class="text-xs text-white/40">Enable the Patient Display screen</p>
                        </div>
                        <button type="button" @click="display_enabled = !display_enabled"
                                :class="display_enabled ? 'bg-indigo-500' : 'bg-white/10'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                            <span :class="display_enabled ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                        <input type="hidden" name="display_enabled" :value="display_enabled ? 1 : 0">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-white/80">Fallback TTS</label>
                            <p class="text-xs text-white/40">Use Google Translate if primary provider fails</p>
                        </div>
                        <button type="button" @click="tts_fallback_enabled = !tts_fallback_enabled"
                                :class="tts_fallback_enabled ? 'bg-indigo-500' : 'bg-white/10'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                            <span :class="tts_fallback_enabled ? 'translate-x-6' : 'translate-x-1'"
                                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                        </button>
                        <input type="hidden" name="tts_fallback_enabled" :value="tts_fallback_enabled ? 1 : 0">
                    </div>
                </div>
            </div>

            {{-- TTS Provider --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white/90 mb-4">TTS Provider</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Provider</label>
                        <select name="tts_provider" x-model="tts_provider"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @foreach ($providers as $key => $provider)
                                <option value="{{ $key }}" class="bg-gray-800">{{ $provider['name'] }} {{ $provider['requires_key'] ? '(API Key)' : '(Free)' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="tts_provider !== 'google_translate'" x-transition>
                        <label class="block text-sm font-medium text-white/70 mb-1">API Key</label>
                        <input type="password" name="tts_api_key" value="{{ $settings->tts_api_key }}"
                               placeholder="Enter API key for selected provider"
                               class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <p class="text-xs text-white/40 mt-1">Required for Google Cloud, Azure, and ElevenLabs. Not needed for Google Translate.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Voice</label>
                        <select name="tts_voice" x-model="tts_voice"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <template x-if="tts_provider === 'google_translate'">
                                <select>
                                    <option value="bn-BD" class="bg-gray-800">Bengali (bn-BD)</option>
                                    <option value="bn" class="bg-gray-800">Bengali (bn)</option>
                                </select>
                            </template>
                            <template x-if="tts_provider === 'google_cloud'">
                                <div>
                                    <option value="bn-BD-Wavenet-A" class="bg-gray-800">Wavenet A (Female)</option>
                                    <option value="bn-BD-Wavenet-B" class="bg-gray-800">Wavenet B (Male)</option>
                                    <option value="bn-BD-Standard-A" class="bg-gray-800">Standard A (Female)</option>
                                    <option value="bn-BD-Standard-B" class="bg-gray-800">Standard B (Male)</option>
                                </div>
                            </template>
                            <template x-if="tts_provider === 'microsoft_azure'">
                                <div>
                                    <option value="bn-BD-NabilaNeural" class="bg-gray-800">Nabila (Female)</option>
                                    <option value="bn-BD-BashirNeural" class="bg-gray-800">Bashir (Male)</option>
                                </div>
                            </template>
                            <template x-if="tts_provider === 'elevenlabs'">
                                <div>
                                    <option value="21m00Tcm4TlvDq8ikWAM" class="bg-gray-800">Rachel (Female)</option>
                                    <option value="ErXwobaYiN019PkySvjV" class="bg-gray-800">Antoni (Male)</option>
                                </div>
                            </template>
                        </select>
                        <p class="text-xs text-white/40 mt-1">The voice used for Bengali announcements.</p>
                    </div>
                </div>
            </div>

            {{-- Voice Parameters --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white/90 mb-4">Voice Parameters</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">
                            Language: <span class="text-white/90" x-text="tts_language"></span>
                        </label>
                        <select name="tts_language"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="bn-BD" class="bg-gray-800" {{ $settings->tts_language === 'bn-BD' ? 'selected' : '' }}>Bengali (Bangladesh) - bn-BD</option>
                            <option value="bn-IN" class="bg-gray-800" {{ $settings->tts_language === 'bn-IN' ? 'selected' : '' }}>Bengali (India) - bn-IN</option>
                            <option value="bn" class="bg-gray-800" {{ $settings->tts_language === 'bn' ? 'selected' : '' }}>Bengali - bn</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">
                            Speed: <span class="text-white/90" x-text="tts_speed + 'x'"></span>
                        </label>
                        <input type="range" name="tts_speed" x-model="tts_speed" min="0.5" max="2.0" step="0.1"
                               class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                        <div class="flex justify-between text-xs text-white/30 mt-1">
                            <span>0.5x (Slow)</span>
                            <span>1.0x (Normal)</span>
                            <span>2.0x (Fast)</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">
                            Volume: <span class="text-white/90" x-text="Math.round(tts_volume * 100) + '%'"></span>
                        </label>
                        <input type="range" name="tts_volume" x-model="tts_volume" min="0.1" max="2.0" step="0.1"
                               class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-indigo-500">
                        <div class="flex justify-between text-xs text-white/30 mt-1">
                            <span>10% (Quiet)</span>
                            <span>100% (Normal)</span>
                            <span>200% (Loud)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Test Panel --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white/90 mb-4">Test TTS</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Test Text</label>
                        <input type="text" x-model="testText"
                               class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               placeholder="নাসরিন সুলতানা">
                    </div>

                    <button type="button" @click="testTts()" :disabled="testing"
                            class="w-full px-4 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-all flex items-center justify-center gap-2">
                        <template x-if="!testing">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                                Test Voice
                            </span>
                        </template>
                        <template x-if="testing">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Generating...
                            </span>
                        </template>
                    </button>

                    <template x-if="testResult">
                        <div class="p-3 rounded-lg text-sm"
                             :class="testResult.success ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/20 text-rose-400'">
                            <template x-if="testResult.success">
                                <div>
                                    <p class="font-medium">Success!</p>
                                    <p class="text-xs mt-1" x-text="'Provider: ' + testResult.provider + ' | Time: ' + testResult.elapsed_ms + 'ms | Size: ' + testResult.audio_size + ' bytes'"></p>
                                    <audio x-ref="testAudio" :src="testResult.audio_url" class="mt-2 w-full"></audio>
                                    <button type="button" @click="$refs.testAudio.play()" class="mt-1 text-xs text-emerald-300 hover:text-emerald-200">Play Again</button>
                                </div>
                            </template>
                            <template x-if="!testResult.success">
                                <div>
                                    <p class="font-medium">Failed</p>
                                    <p class="text-xs mt-1" x-text="testResult.message || 'TTS provider unavailable'"></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <button type="button" @click="clearCache()" class="w-full px-4 py-2 text-sm text-white/60 bg-white/5 hover:bg-white/10 rounded-lg transition-all">
                        Clear Audio Cache
                    </button>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-all">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
function voiceSettings() {
    return {
        voice_enabled: @json($settings->voice_enabled),
        display_enabled: @json($settings->display_enabled),
        tts_provider: @json($settings->tts_provider ?? 'google_translate'),
        tts_voice: @json($settings->tts_voice ?? 'bn-BD'),
        tts_language: @json($settings->tts_language ?? 'bn-BD'),
        tts_speed: @json($settings->tts_speed ?? 1.0),
        tts_volume: @json($settings->tts_volume ?? 1.0),
        tts_fallback_enabled: @json($settings->tts_fallback_enabled ?? true),
        testText: 'নাসরিন সুলতানা',
        testing: false,
        testResult: null,

        async testTts() {
            this.testing = true;
            this.testResult = null;
            try {
                const response = await fetch('{{ route("admin.smart-serial-voice.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        doctor_id: {{ $doctor->id }},
                        text: this.testText,
                    }),
                });
                this.testResult = await response.json();
                if (this.testResult.success && this.testResult.audio_url) {
                    this.$nextTick(() => {
                        const audio = this.$refs.testAudio;
                        if (audio) audio.play();
                    });
                }
            } catch (e) {
                this.testResult = { success: false, message: 'Network error' };
            }
            this.testing = false;
        },

        async clearCache() {
            try {
                const response = await fetch('{{ route("admin.smart-serial-voice.clear-cache") }}', {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                alert(data.message || 'Cache cleared');
            } catch (e) {
                alert('Failed to clear cache');
            }
        }
    };
}
</script>
@endsection
