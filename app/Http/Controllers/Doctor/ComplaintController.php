<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');

        $complaints = Complaint::query()
            ->when($term, fn($q) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('used_count', 'desc')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'used_count']);

        return response()->json($complaints);
    }

    public function popular(): JsonResponse
    {
        $complaints = Complaint::popular()->limit(20)->get(['id', 'name', 'used_count']);

        return response()->json($complaints);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $complaint = Complaint::firstOrCreate(
            ['name' => trim($data['name'])],
            ['used_count' => 0]
        );

        if (!$complaint->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Complaint already exists.',
                'complaint' => $complaint,
                'exists' => true,
            ]);
        }

        return response()->json([
            'message' => 'Complaint created successfully.',
            'complaint' => $complaint,
            'exists' => false,
        ], 201);
    }
}
