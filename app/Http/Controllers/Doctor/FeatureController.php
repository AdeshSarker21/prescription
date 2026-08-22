<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\AnesthesiaRecord;
use App\Models\DrugHistory;
use App\Models\FamilyHistory;
use App\Models\MenstrualHistory;
use App\Models\OtNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FeatureController extends Controller
{
    private const TYPES = [
        'family_history' => FamilyHistory::class,
        'menstrual_history' => MenstrualHistory::class,
        'drug_history' => DrugHistory::class,
        'ot_note' => OtNote::class,
        'anesthesia' => AnesthesiaRecord::class,
    ];

    private const RULES = [
        'family_history' => [
            'name' => ['required', 'string', 'max:255'],
            'relation' => ['nullable', 'string', 'max:255'],
        ],
        'menstrual_history' => [
            'lmp' => ['nullable', 'string', 'max:255'],
            'cycle' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'flow' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ],
        'drug_history' => [
            'name' => ['required', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:255'],
        ],
        'ot_note' => [
            'procedure' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ],
        'anesthesia' => [
            'type' => ['required', 'string', 'max:255'],
            'agent' => ['nullable', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ],
    ];

    public function store(Request $request, string $type): JsonResponse
    {
        if (!isset(self::TYPES[$type]) || !isset(self::RULES[$type])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid feature type.',
            ], 400);
        }

        if ($type === 'menstrual_history') {
            $trimmed = collect(['lmp', 'cycle', 'duration', 'flow', 'notes'])
                ->map(fn($key) => trim((string) $request->input($key)))
                ->reject(fn($value) => $value === '')
                ->count();

            if ($trimmed === 0) {
                throw ValidationException::withMessages([
                    'flow' => 'Please fill in at least one menstrual history field.',
                ]);
            }
        }

        $validated = $request->validate(self::RULES[$type]);

        $validated = collect($validated)->map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        })->all();

        $name = $this->deriveName($type, $validated);
        if (trim($name) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Nothing to save. Please enter some details.',
            ], 422);
        }

        $modelClass = self::TYPES[$type];
        $item = $modelClass::findByNameOrCreate(trim($name), auth()->id());
        $item->increment('used_count');

        return response()->json([
            'success' => true,
            'message' => 'Saved successfully.',
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
            ],
        ]);
    }

    private function deriveName(string $type, array $data): string
    {
        return match ($type) {
            'family_history' => $data['name'] ?? '',
            'drug_history' => $data['name'] ?? '',
            'ot_note' => $data['procedure'] ?? '',
            'anesthesia' => filled($data['type'] ?? null) ? $data['type'] : ($data['agent'] ?? ''),
            'menstrual_history' => implode('; ', array_filter([
                filled($data['flow'] ?? null) ? 'Flow: ' . $data['flow'] : '',
                filled($data['cycle'] ?? null) ? 'Cycle: ' . $data['cycle'] : '',
                filled($data['duration'] ?? null) ? 'Duration: ' . $data['duration'] : '',
                filled($data['lmp'] ?? null) ? 'LMP: ' . $data['lmp'] : '',
            ])) ?: (filled($data['notes'] ?? null) ? mb_substr($data['notes'], 0, 100) : ''),
            default => '',
        };
    }
}
