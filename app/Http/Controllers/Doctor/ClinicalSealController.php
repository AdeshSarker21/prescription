<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ClinicalSeal;
use App\Models\DoctorItemUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClinicalSealController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('q', '');

        $query = ClinicalSeal::active();

        if ($term !== '') {
            $query->search($term);
        }

        $items = $query->orderBy('used_count', 'desc')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json($items);
    }

    public function popular(): JsonResponse
    {
        $items = ClinicalSeal::active()
            ->orderBy('used_count', 'desc')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json($items);
    }

    public function recent(): JsonResponse
    {
        $doctorId = auth()->id();

        $usages = DoctorItemUsage::where('doctor_id', $doctorId)
            ->where('itemable_type', ClinicalSeal::class)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->pluck('itemable')
            ->filter()
            ->values()
            ->map(fn($item) => $this->formatItem($item));

        return response()->json($usages);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:2000',
            'details' => 'nullable|string|max:5000',
        ]);

        $name = trim($request->input('name'));
        $details = trim($request->input('details', ''));
        $normalizedName = mb_strtolower($name);

        $existing = ClinicalSeal::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->where('doctor_id', auth()->id())
            ->first();

        if ($existing) {
            return response()->json([
                'item' => $this->formatItem($existing),
                'exists' => true,
            ]);
        }

        $item = ClinicalSeal::create([
            'name' => $name,
            'details' => $details ?: null,
            'doctor_id' => auth()->id(),
            'created_by' => auth()->id(),
            'used_count' => 1,
        ]);

        return response()->json([
            'item' => $this->formatItem($item),
            'exists' => false,
        ]);
    }

    public function update(Request $request, ClinicalSeal $clinicalSeal): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only admin can edit clinical seals.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:2000',
            'details' => 'nullable|string|max:5000',
        ]);

        $name = trim($request->input('name'));
        $details = trim($request->input('details', ''));
        $normalizedName = mb_strtolower($name);

        $existing = ClinicalSeal::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->where('doctor_id', auth()->id())
            ->where('id', '!=', $clinicalSeal->id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'A clinical seal with this name already exists.'], 422);
        }

        $clinicalSeal->update([
            'name' => $name,
            'details' => $details ?: null,
        ]);

        return response()->json([
            'item' => $this->formatItem($clinicalSeal),
        ]);
    }

    public function destroy(ClinicalSeal $clinicalSeal): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Only admin can delete clinical seals.'], 403);
        }

        $clinicalSeal->delete();

        return response()->json(['success' => true]);
    }

    public function trackUsage(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|integer']);

        $item = ClinicalSeal::findOrFail($request->input('id'));
        $item->increment('used_count');

        DoctorItemUsage::updateOrCreate(
            [
                'doctor_id' => auth()->id(),
                'itemable_type' => ClinicalSeal::class,
                'itemable_id' => $item->id,
            ],
            ['updated_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    private function formatItem($item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'details' => $item->details ?? '',
            'used_count' => $item->used_count ?? 0,
            'created_by' => $item->created_by,
        ];
    }
}
