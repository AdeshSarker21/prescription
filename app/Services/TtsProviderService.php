<?php

namespace App\Services;

use App\Models\SmartSerialSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TtsProviderService
{
    private ?SmartSerialSetting $settings;
    private string $cacheDir = 'tts-cache';
    private string $publicCachePath;

    public function __construct(?SmartSerialSetting $settings = null)
    {
        $this->settings = $settings;
        $this->publicCachePath = public_path($this->cacheDir);
        if (!is_dir($this->publicCachePath)) {
            mkdir($this->publicCachePath, 0755, true);
        }
    }

    public function setSettings(SmartSerialSetting $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    public function getAvailableProviders(): array
    {
        return [
            'google_translate' => [
                'name' => 'Google Translate TTS',
                'description' => 'Free Bengali TTS via Google Translate (no API key needed)',
                'requires_key' => false,
                'languages' => ['bn-BD', 'bn', 'en'],
            ],
            'google_cloud' => [
                'name' => 'Google Cloud Text-to-Speech',
                'description' => 'High quality Bengali TTS via Google Cloud API (requires API key)',
                'requires_key' => true,
                'languages' => ['bn-BD', 'bn-IN', 'en-US'],
            ],
            'microsoft_azure' => [
                'name' => 'Microsoft Azure Cognitive Services',
                'description' => 'High quality Bengali TTS via Azure (requires API key)',
                'requires_key' => true,
                'languages' => ['bn-BD', 'bn-IN'],
            ],
            'elevenlabs' => [
                'name' => 'ElevenLabs',
                'description' => 'Premium AI voice TTS (requires API key)',
                'requires_key' => true,
                'languages' => ['bn', 'en'],
            ],
        ];
    }

    public function generateAudio(string $text, string $type = 'announcement'): ?string
    {
        if (!$this->settings) {
            Log::warning('[TTS] No settings configured');
            return null;
        }

        $cacheKey = $this->generateCacheKey($text, $type);

        $cachedPath = $this->getCachedAudio($cacheKey);
        if ($cachedPath) {
            Log::info('[TTS] Cache hit', ['key' => $cacheKey]);
            return $cachedPath;
        }

        $provider = $this->settings->tts_provider ?? 'google_translate';

        $audioContent = match ($provider) {
            'google_translate' => $this->generateViaGoogleTranslate($text),
            'google_cloud' => $this->generateViaGoogleCloud($text),
            'microsoft_azure' => $this->generateViaMicrosoftAzure($text),
            'elevenlabs' => $this->generateViaElevenLabs($text),
            default => $this->generateViaGoogleTranslate($text),
        };

        if ($audioContent) {
            $path = $this->storeAudio($audioContent, $cacheKey);
            if ($path) {
                $this->setCachedAudio($cacheKey, $path);
                return $path;
            }
        }

        if ($this->settings->tts_fallback_enabled && $provider !== 'google_translate') {
            Log::warning('[TTS] Primary provider failed, falling back to Google Translate');
            $audioContent = $this->generateViaGoogleTranslate($text);
            if ($audioContent) {
                $path = $this->storeAudio($audioContent, $cacheKey . '_fallback');
                if ($path) {
                    $this->setCachedAudio($cacheKey, $path);
                    return $path;
                }
            }
        }

        Log::error('[TTS] All providers failed for text: ' . mb_substr($text, 0, 50));
        return null;
    }

    private function generateViaGoogleTranslate(string $text): ?string
    {
        try {
            // Google Translate TTS has a ~200 char limit per request
            $chunks = $this->splitTextForGoogleTts($text);
            $allAudio = '';

            foreach ($chunks as $chunk) {
                $encodedText = rawurlencode($chunk);
                $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=bn-BD&client=tw-ob&q={$encodedText}";

                $response = Http::timeout(15)
                    ->withOptions(['verify' => false])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Referer' => 'https://translate.google.com/',
                    ])
                    ->get($url);

                if ($response->successful() && strlen($response->body()) > 500) {
                    $allAudio .= $response->body();
                } else {
                    Log::warning('[TTS] Google Translate TTS chunk failed', [
                        'status' => $response->status(),
                        'chunk' => mb_substr($chunk, 0, 30),
                    ]);
                    // If any chunk fails, try file_get_contents fallback
                    try {
                        $ctx = stream_context_create([
                            'http' => [
                                'method' => 'GET',
                                'header' => "User-Agent: Mozilla/5.0\r\nReferer: https://translate.google.com/\r\n",
                                'timeout' => 15,
                                'ignore_errors' => true,
                                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                            ],
                        ]);
                        $audio = @file_get_contents($url, false, $ctx);
                        if ($audio && strlen($audio) > 500) {
                            $allAudio .= $audio;
                        }
                    } catch (\Exception $e2) {
                        Log::error('[TTS] file_get_contents fallback failed: ' . $e2->getMessage());
                    }
                }
            }

            if (strlen($allAudio) > 1000) {
                Log::info('[TTS] Google Translate TTS success', [
                    'text_length' => mb_strlen($text),
                    'chunks' => count($chunks),
                    'audio_size' => strlen($allAudio),
                ]);
                return $allAudio;
            }

            Log::warning('[TTS] Google Translate TTS empty result', ['chunks' => count($chunks)]);
            return null;
        } catch (\Exception $e) {
            Log::error('[TTS] Google Translate TTS error: ' . $e->getMessage());
            return null;
        }
    }

    private function splitTextForGoogleTts(string $text): array
    {
        $maxLen = 180;
        if (mb_strlen($text) <= $maxLen) {
            return [$text];
        }

        $chunks = [];
        $sentences = preg_split('/(?<=[।!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $current = '';
        foreach ($sentences as $sentence) {
            if (mb_strlen($current) + mb_strlen($sentence) + 1 > $maxLen) {
                if ($current !== '') {
                    $chunks[] = $current;
                }
                $current = $sentence;
            } else {
                $current = $current === '' ? $sentence : $current . ' ' . $sentence;
            }
        }
        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }

    private function generateViaGoogleCloud(string $text): ?string
    {
        $apiKey = $this->settings->tts_api_key;
        if (!$apiKey) {
            Log::warning('[TTS] Google Cloud API key not configured');
            return null;
        }

        try {
            $voiceName = $this->settings->tts_voice ?? 'bn-BD-Wavenet-A';
            $speed = $this->settings->tts_speed ?? 1.0;
            $volumeGainDb = ($this->settings->tts_volume ?? 1.0) * 6 - 6;

            $response = Http::timeout(30)
                ->post("https://texttospeech.googleapis.com/v1/text:synthesize?key={$apiKey}", [
                    'input' => ['ssml' => "<speak><prosody rate=\"{$speed}\" volume=\"{$volumeGainDb}\">{$text}</prosody></speak>"],
                    'voice' => [
                        'languageCode' => 'bn-BD',
                        'name' => $voiceName,
                        'ssmlGender' => 'FEMALE',
                    ],
                    'audioConfig' => [
                        'audioEncoding' => 'MP3',
                        'sampleRateHertz' => 24000,
                    ],
                ]);

            if ($response->successful() && isset($response['audioContent'])) {
                $audio = base64_decode($response['audioContent']);
                Log::info('[TTS] Google Cloud TTS success', ['size' => strlen($audio)]);
                return $audio;
            }

            Log::warning('[TTS] Google Cloud TTS failed', ['status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('[TTS] Google Cloud TTS error: ' . $e->getMessage());
            return null;
        }
    }

    private function generateViaMicrosoftAzure(string $text): ?string
    {
        $apiKey = $this->settings->tts_api_key;
        if (!$apiKey) {
            Log::warning('[TTS] Azure API key not configured');
            return null;
        }

        try {
            $region = $this->settings->tts_voice ?? 'southeastasia';
            $voiceName = 'bn-BD-NabilaNeural';
            $rate = $this->settings->tts_speed ?? 1.0;
            $volume = round(($this->settings->tts_volume ?? 1.0) * 100);

            $ssml = <<<SSML
            <speak version="1.0" xmlns="http://www.w3.org/2001/10/synthesis" xml:lang="bn-BD">
                <voice name="{$voiceName}">
                    <prosody rate="{$rate}%" volume="{$volume}%">
                        {$text}
                    </prosody>
                </voice>
            </speak>
            SSML;

            $response = Http::timeout(30)
                ->withHeaders([
                    'Ocp-Apim-Subscription-Key' => $apiKey,
                    'Content-Type' => 'application/ssml+xml',
                    'X-Microsoft-OutputFormat' => 'audio-24khz-96kbitrate-mono-mp3',
                ])
                ->post("https://{$region}.tts.speech.microsoft.com/cognitiveservices/v1", $ssml);

            if ($response->successful()) {
                Log::info('[TTS] Azure TTS success', ['size' => strlen($response->body())]);
                return $response->body();
            }

            Log::warning('[TTS] Azure TTS failed', ['status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('[TTS] Azure TTS error: ' . $e->getMessage());
            return null;
        }
    }

    private function generateViaElevenLabs(string $text): ?string
    {
        $apiKey = $this->settings->tts_api_key;
        if (!$apiKey) {
            Log::warning('[TTS] ElevenLabs API key not configured');
            return null;
        }

        try {
            $voiceId = '21m00Tcm4TlvDq8ikWAM';
            $modelId = 'eleven_multilingual_v2';

            $response = Http::timeout(30)
                ->withHeaders([
                    'xi-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                    'text' => $text,
                    'model_id' => $modelId,
                    'voice_settings' => [
                        'stability' => 0.5,
                        'similarity_boost' => 0.75,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('[TTS] ElevenLabs TTS success', ['size' => strlen($response->body())]);
                return $response->body();
            }

            Log::warning('[TTS] ElevenLabs TTS failed', ['status' => $response->status()]);
            return null;
        } catch (\Exception $e) {
            Log::error('[TTS] ElevenLabs TTS error: ' . $e->getMessage());
            return null;
        }
    }

    private function generateCacheKey(string $text, string $type): string
    {
        $provider = $this->settings->tts_provider ?? 'google_translate';
        $speed = $this->settings->tts_speed ?? 1.0;
        $volume = $this->settings->tts_volume ?? 1.0;
        $raw = "{$provider}_{$type}_{$text}_{$speed}_{$volume}";
        return md5($raw);
    }

    private function getCachedAudio(string $cacheKey): ?string
    {
        $file = $this->publicCachePath . "/{$cacheKey}.mp3";
        if (file_exists($file) && filesize($file) > 500) {
            return $file;
        }
        return null;
    }

    private function setCachedAudio(string $cacheKey, string $path): void
    {
        Cache::put("tts_cache_{$cacheKey}", $path, now()->addDays(30));
    }

    private function storeAudio(string $content, string $cacheKey): ?string
    {
        try {
            $file = $this->publicCachePath . "/{$cacheKey}.mp3";
            file_put_contents($file, $content);
            return $file;
        } catch (\Exception $e) {
            Log::error('[TTS] Failed to store audio: ' . $e->getMessage());
            return null;
        }
    }

    public function getAudioDataUri(string $text, string $type = 'announcement'): ?string
    {
        $cacheKey = $this->generateCacheKey($text, $type);
        $cached = $this->getCachedAudio($cacheKey);
        if ($cached) {
            $content = file_get_contents($cached);
            if ($content) {
                return 'data:audio/mpeg;base64,' . base64_encode($content);
            }
        }
        return null;
    }

    public function clearCache(): int
    {
        $files = glob($this->publicCachePath . '/*.mp3');
        $count = 0;
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }

    public function testProvider(string $text = 'নমস্কার, আমি বাংলায় কথা বলি।'): array
    {
        $startTime = microtime(true);
        $audio = $this->generateAudio($text, 'test');
        $elapsed = round((microtime(true) - $startTime) * 1000);

        $audioSize = 0;
        $audioDataUri = null;
        if ($audio && file_exists($audio)) {
            $audioSize = filesize($audio);
            $content = file_get_contents($audio);
            if ($content) {
                $audioDataUri = 'data:audio/mpeg;base64,' . base64_encode($content);
            }
        }

        return [
            'success' => $audio !== null,
            'provider' => $this->settings->tts_provider ?? 'unknown',
            'elapsed_ms' => $elapsed,
            'audio_size' => $audioSize,
            'audio_url' => $audioDataUri,
        ];
    }
}
