<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartSerialSetting;
use App\Models\User;
use App\Services\TtsProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmartSerialVoiceSettingController extends Controller
{
    protected TtsProviderService $ttsService;

    public function __construct(TtsProviderService $ttsService)
    {
        $this->ttsService = $ttsService;
    }

    public function index()
    {
        $doctors = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->with('smartSerialSetting')
            ->orderBy('name')
            ->get();

        $providers = $this->ttsService->getAvailableProviders();

        return view('admin.smart-serial-voice-settings.index', compact('doctors', 'providers'));
    }

    public function edit($doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $settings = SmartSerialSetting::firstOrCreate(
            ['doctor_id' => $doctorId],
            [
                'voice_enabled' => true,
                'display_enabled' => true,
                'tts_provider' => 'google_translate',
                'tts_voice' => 'bn-BD',
                'tts_speed' => 1.0,
                'tts_volume' => 1.0,
                'tts_language' => 'bn-BD',
                'tts_fallback_enabled' => true,
            ]
        );

        $providers = $this->ttsService->getAvailableProviders();

        return view('admin.smart-serial-voice-settings.edit', compact('doctor', 'settings', 'providers'));
    }

    public function update(Request $request, $doctorId)
    {
        $validated = $request->validate([
            'voice_enabled' => 'required|boolean',
            'display_enabled' => 'required|boolean',
            'tts_provider' => 'required|string|in:google_translate,google_cloud,microsoft_azure,elevenlabs',
            'tts_api_key' => 'nullable|string|max:500',
            'tts_voice' => 'required|string|max:50',
            'tts_speed' => 'required|numeric|min:0.5|max:2.0',
            'tts_volume' => 'required|numeric|min:0.1|max:2.0',
            'tts_language' => 'required|string|max:10',
            'tts_fallback_enabled' => 'required|boolean',
        ]);

        $settings = SmartSerialSetting::updateOrCreate(
            ['doctor_id' => $doctorId],
            $validated
        );

        return redirect()->route('admin.smart-serial-voice.edit', $doctorId)
            ->with('success', 'Voice settings updated successfully.');
    }

    public function testTts(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'text' => 'required|string|max:200',
        ]);

        $settings = SmartSerialSetting::where('doctor_id', $validated['doctor_id'])->first();
        if (!$settings) {
            return response()->json(['success' => false, 'message' => 'No settings found. Save settings first.'], 404);
        }

        $this->ttsService->setSettings($settings);
        $result = $this->ttsService->testProvider($validated['text']);

        return response()->json($result);
    }

    public function clearCache()
    {
        $count = $this->ttsService->clearCache();
        return redirect()->route('admin.smart-serial-voice.index')
            ->with('success', "Cleared {$count} cached audio files.");
    }
}
