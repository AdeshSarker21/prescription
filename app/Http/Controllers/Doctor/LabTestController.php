<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\LaboratoryTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabTestController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('q', '');

        $tests = LaboratoryTest::query()
            ->when($term, fn($q) => $q->where('test_name', 'like', "%{$term}%"))
            ->orderBy('used_count', 'desc')
            ->orderBy('test_name')
            ->limit(20)
            ->get(['id', 'test_name', 'used_count']);

        return response()->json($tests);
    }

    public function popular(): JsonResponse
    {
        $tests = LaboratoryTest::popular()->limit(20)->get(['id', 'test_name', 'used_count']);

        return response()->json($tests);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'test_name' => 'required|string|max:255',
        ]);

        $test = LaboratoryTest::firstOrCreate(
            ['test_name' => trim($data['test_name'])],
            ['used_count' => 0]
        );

        if (!$test->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Test already exists.',
                'test' => $test,
                'exists' => true,
            ]);
        }

        return response()->json([
            'message' => 'Test created successfully.',
            'test' => $test,
            'exists' => false,
        ], 201);
    }
}
