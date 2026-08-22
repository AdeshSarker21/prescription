<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use App\Models\AnesthesiaRecord;
use App\Models\ClinicalFeature;
use App\Models\Complaint;
use App\Models\DoctorItemUsage;
use App\Models\DrugHistory;
use App\Models\FamilyHistory;
use App\Models\LaboratoryTest;
use App\Models\MedicalHistoryCondition;
use App\Models\MenstrualHistory;
use App\Models\OtNote;
use App\Models\Procedure;
use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemSearchController extends Controller
{
    private array $typeMap = [
        'complaint' => Complaint::class,
        'test' => LaboratoryTest::class,
        'medical_history' => MedicalHistoryCondition::class,
        'advice' => Advice::class,
        'clinical_feature' => ClinicalFeature::class,
        'family_history' => FamilyHistory::class,
        'menstrual_history' => MenstrualHistory::class,
        'drug_history' => DrugHistory::class,
        'ot_note' => OtNote::class,
        'anesthesia' => AnesthesiaRecord::class,
        'procedure' => Procedure::class,
        'treatment_plan' => TreatmentPlan::class,
    ];

    private string $nameAttribute = '';

    private function getModelClass(string $type): ?string
    {
        return $this->typeMap[$type] ?? null;
    }

    private function getNameColumn(string $type): string
    {
        return match ($type) {
            'test' => 'test_name',
            default => 'name',
        };
    }

    public function search(Request $request, string $type): JsonResponse
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $query = $modelClass::active();

        $term = $request->input('q', '');
        if ($term !== '') {
            $column = $this->getNameColumn($type);
            $query->where($column, 'like', "%{$term}%");
        }

        $items = $query->orderBy('used_count', 'desc')
            ->orderBy($this->getNameColumn($type))
            ->limit(20)
            ->get()
            ->map(fn($item) => $this->formatItem($item, $type));

        return response()->json($items);
    }

    public function popular(Request $request, string $type): JsonResponse
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $items = $modelClass::active()
            ->orderBy('used_count', 'desc')
            ->orderBy($this->getNameColumn($type))
            ->limit(20)
            ->get()
            ->map(fn($item) => $this->formatItem($item, $type));

        return response()->json($items);
    }

    public function recent(Request $request, string $type): JsonResponse
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $doctorId = auth()->id();

        $usages = DoctorItemUsage::where('doctor_id', $doctorId)
            ->where('itemable_type', $modelClass)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->pluck('itemable')
            ->filter()
            ->values()
            ->map(fn($item) => $this->formatItem($item, $type));

        return response()->json($usages);
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $name = trim($request->input('name'));
        $column = $this->getNameColumn($type);
        $normalizedName = mb_strtolower($name);

        $existing = $modelClass::whereRaw("LOWER({$column}) = ?", [$normalizedName])->first();

        if ($existing) {
            return response()->json([
                'item' => $this->formatItem($existing, $type),
                'exists' => true,
            ]);
        }

        $item = $modelClass::create([
            $column => $name,
            'created_by' => auth()->id(),
            'used_count' => 1,
        ]);

        return response()->json([
            'item' => $this->formatItem($item, $type),
            'exists' => false,
        ]);
    }

    public function trackUsage(Request $request, string $type): JsonResponse
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $request->validate(['id' => 'required|integer']);

        $item = $modelClass::findOrFail($request->input('id'));
        $column = $this->getNameColumn($type);

        $item->increment('used_count');

        DoctorItemUsage::updateOrCreate(
            [
                'doctor_id' => auth()->id(),
                'itemable_type' => $modelClass,
                'itemable_id' => $item->id,
            ],
            ['updated_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    private function formatItem($item, string $type): array
    {
        $column = $this->getNameColumn($type);
        return [
            'id' => $item->id,
            'name' => $item->$column,
            'used_count' => $item->used_count ?? 0,
        ];
    }
}
