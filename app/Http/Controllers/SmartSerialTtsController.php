<?php

namespace App\Http\Controllers;

use App\Models\PatientQueue;
use App\Models\SmartSerialAnnouncementHistory;
use App\Models\SmartSerialSetting;
use App\Services\TtsProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SmartSerialTtsController extends Controller
{
    protected TtsProviderService $ttsService;

    public function __construct(TtsProviderService $ttsService)
    {
        $this->ttsService = $ttsService;
    }

    public function generateAnnouncement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'queue_id' => 'required|exists:patient_queues,id',
            'type' => 'required|string|in:preparing,calling,inside,emergency,recall,completed',
        ]);

        $queue = PatientQueue::with(['patient', 'session.doctor'])->findOrFail($validated['queue_id']);
        $patient = $queue->patient;
        $session = $queue->session;
        $doctor = $session->doctor;

        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Patient not found'], 404);
        }

        $settings = SmartSerialSetting::where('doctor_id', $doctor->id)->first();
        if (!$settings || !$settings->voice_enabled) {
            return response()->json(['success' => false, 'message' => 'Voice is disabled'], 400);
        }

        $text = $this->buildAnnouncementText($validated['type'], $patient, $doctor);

        $this->ttsService->setSettings($settings);
        $audioPath = $this->ttsService->generateAudio($text, $validated['type']);

        if (!$audioPath || !file_exists($audioPath)) {
            SmartSerialAnnouncementHistory::create([
                'serial_session_id' => $session->id,
                'patient_queue_id' => $queue->id,
                'patient_id' => $patient->id,
                'announcement_type' => $validated['type'],
                'text_spoken' => $text,
                'tts_provider_used' => $settings->tts_provider,
                'success' => false,
                'error_message' => 'Failed to generate audio',
                'announced_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate audio. TTS provider may be unavailable.',
            ], 500);
        }

        SmartSerialAnnouncementHistory::create([
            'serial_session_id' => $session->id,
            'patient_queue_id' => $queue->id,
            'patient_id' => $patient->id,
            'announcement_type' => $validated['type'],
            'text_spoken' => $text,
            'tts_provider_used' => $settings->tts_provider,
            'audio_cache_key' => basename($audioPath, '.mp3'),
            'success' => true,
            'announced_at' => now(),
        ]);

        $audioSize = 0;
        $audioDataUri = null;
        if (file_exists($audioPath)) {
            $audioSize = filesize($audioPath);
            $content = file_get_contents($audioPath);
            if ($content) {
                $audioDataUri = 'data:audio/mpeg;base64,' . base64_encode($content);
            }
        }

        return response()->json([
            'success' => true,
            'audio_url' => $audioDataUri,
            'audio_size' => $audioSize,
            'text' => $text,
            'type' => $validated['type'],
            'provider' => $settings->tts_provider,
        ]);
    }

    public function getAudioFile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:200',
            'type' => 'nullable|string|max:50',
        ]);

        $type = $validated['type'] ?? 'announcement';

        $doctorId = $request->query('doctor_id');
        $settings = null;
        if ($doctorId) {
            $settings = SmartSerialSetting::where('doctor_id', $doctorId)->first();
        }

        if ($settings) {
            $this->ttsService->setSettings($settings);
        }

        $audioPath = $this->ttsService->generateAudio($validated['text'], $type);

        if (!$audioPath) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate audio',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'audio_url' => asset('storage/' . $audioPath),
        ]);
    }

    public function checkCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $type = $validated['type'] ?? 'announcement';
        $url = $this->ttsService->getCachedAudioUrl($validated['text'], $type);

        return response()->json([
            'cached' => $url !== null,
            'audio_url' => $url,
        ]);
    }

    private function buildAnnouncementText(string $type, $patient, $doctor): string
    {
        $name = $patient->name ?? 'রোগী';
        $gender = $patient->gender ?? 'male';
        $prefix = $gender === 'female' ? 'জনাবা' : 'জনাব';

        return match ($type) {
            'preparing' => "পরবর্তী সিরিয়ালের জন্য প্রস্তুত থাকুন, {$prefix} {$name}।",
            'calling' => "{$prefix} {$name}, আপনি এবার ভিতরে প্রবেশ করুন।",
            'inside' => "{$prefix} {$name}, ধন্যবাদ।",
            'emergency' => "জরুরি! {$prefix} {$name}, আপনাকে জরুরি ভিতরে প্রবেশ করুন।",
            'recall' => "{$prefix} {$name}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।",
            'completed' => "{$prefix} {$name}, ধন্যবাদ।",
            default => "{$prefix} {$name}, আপনি এবার ভিতরে প্রবেশ করুন।",
        };
    }
}
