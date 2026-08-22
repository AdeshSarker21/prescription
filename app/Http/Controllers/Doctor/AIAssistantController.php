<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\AIMedicalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AIAssistantController extends Controller
{
    public function __construct(
        protected AIMedicalService $aiService
    ) {}

    public function index(): View
    {
        $patients = Patient::where('doctor_id', auth()->id())->get();
        return view('doctor.ai-assistant.index', compact('patients'));
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'patient_id' => 'nullable|integer',
        ]);

        try {
            $doctorId = auth()->id();
            $patientId = $request->input('patient_id');

            $response = $this->aiService->chat(
                $request->input('message'),
                $doctorId,
                $patientId
            );

            return response()->json($response);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat error', ['message' => $e->getMessage()]);
            return response()->json([
                'reply' => 'An error occurred while processing your request. Please try again.',
                'suggestions' => ['diagnosis' => [], 'medicines' => [], 'tests' => [], 'advice' => [], 'follow_up' => ''],
                'warnings' => [],
                'drug_interactions' => [],
                'disclaimer' => config('ai.disclaimer', ''),
            ]);
        }
    }

    public function suggestDiagnosis(Request $request): JsonResponse
    {
        $request->validate([
            'complaints' => 'required|array|min:1',
            'complaints.*' => 'string|max:255',
            'patient_id' => 'nullable|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->suggestDiagnosis(
                $request->input('complaints'),
                auth()->id(),
                $request->input('patient_id')
            );
        });
    }

    public function checkInteractions(Request $request): JsonResponse
    {
        $request->validate([
            'medicines' => 'required|array|min:2',
            'medicines.*' => 'string|max:255',
            'patient_id' => 'nullable|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->checkInteractions(
                $request->input('medicines'),
                auth()->id(),
                $request->input('patient_id')
            );
        });
    }

    public function suggestMedicines(Request $request): JsonResponse
    {
        $request->validate([
            'diagnosis' => 'required|string|max:1000',
            'patient_id' => 'nullable|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->suggestMedicines(
                $request->input('diagnosis'),
                auth()->id(),
                $request->input('patient_id')
            );
        });
    }

    public function suggestTests(Request $request): JsonResponse
    {
        $request->validate([
            'symptoms' => 'required|string|max:1000',
            'diagnosis' => 'nullable|string|max:1000',
            'patient_id' => 'nullable|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->suggestTests(
                $request->input('symptoms'),
                $request->input('diagnosis'),
                auth()->id(),
                $request->input('patient_id')
            );
        });
    }

    public function analyzePatient(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->analyzePatient(
                $request->input('patient_id'),
                auth()->id()
            );
        });
    }

    public function contextualQuery(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:2000',
            'patient_id' => 'nullable|integer',
            'context' => 'nullable|array',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->chat(
                $request->input('query'),
                auth()->id(),
                $request->input('patient_id'),
                $request->input('context', [])
            );
        });
    }

    public function suggestDrug(Request $request): JsonResponse
    {
        $request->validate([
            'symptoms' => 'required|string|max:500',
            'patient_id' => 'nullable|integer',
        ]);

        return $this->safeExecute(function () use ($request) {
            return $this->aiService->suggestMedicines(
                $request->input('symptoms'),
                auth()->id(),
                $request->input('patient_id')
            );
        });
    }

    protected function safeExecute(callable $callback): JsonResponse
    {
        try {
            $response = $callback();
            return response()->json($response);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Assistant error', ['message' => $e->getMessage()]);
            return response()->json([
                'reply' => 'An error occurred while processing your request. Please try again.',
                'suggestions' => ['diagnosis' => [], 'medicines' => [], 'tests' => [], 'advice' => [], 'follow_up' => ''],
                'warnings' => [],
                'drug_interactions' => [],
                'disclaimer' => config('ai.disclaimer', ''),
            ]);
        }
    }
}
