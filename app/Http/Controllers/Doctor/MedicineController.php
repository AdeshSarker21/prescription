<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineSuggestion;
use App\Models\User;
use App\Notifications\Admin\MedicineSuggestionCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $search = $request->get('search');
        $category = $request->get('category');

        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->with('category')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            })
            ->latest()
            ->paginate(15);

        $categories = MedicineCategory::where('is_active', true)->pluck('name', 'id');

        if ($request->ajax()) {
            $html = view('doctor.medicines._cards', compact('medicines'))->render();
            $pagination = $medicines->appends(['search' => $search, 'category' => $category])->links()->toHtml();
            return response()->json(compact('html', 'pagination'));
        }

        return view('doctor.medicines.index', compact('medicines', 'categories', 'search', 'category'));
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load('category');

        return view('doctor.medicines.show', compact('medicine'));
    }

    public function suggest(): View
    {
        $categories = MedicineCategory::where('is_active', true)->get();

        return view('doctor.medicines.suggest', compact('categories'));
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'company_name' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
        ]);

        $existingMedicine = Medicine::findDuplicate(
            $data['name'],
            $data['strength'] ?? null,
            $data['generic_name'] ?? null
        );

        if ($existingMedicine) {
            return redirect()->back()->with('error', 'This medicine already exists (ID: ' . $existingMedicine->id . ').');
        }

        $existingSuggestion = MedicineSuggestion::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data['name']))])
            ->whereRaw('LOWER(COALESCE(strength, \'\')) = ?', [mb_strtolower(trim($data['strength'] ?? ''))])
            ->where('doctor_id', auth()->id())
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingSuggestion) {
            return redirect()->back()->with('error', 'You have already suggested this medicine. Status: ' . ucfirst($existingSuggestion->status) . '.');
        }

        $data['doctor_id'] = auth()->id();
        $data['status'] = 'pending';

        $suggestion = MedicineSuggestion::create($data);

        User::role('admin')->each(fn ($admin) => $admin->notify(new MedicineSuggestionCreated($suggestion)));

        return redirect()->back()->with('success', 'Medicine suggestion submitted successfully.');
    }

    public function suggestions(): View
    {
        $suggestions = MedicineSuggestion::where('doctor_id', auth()->id())
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('doctor.medicines.suggestions', compact('suggestions'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $medicines = Medicine::with('category')
            ->where('status', 'active')
            ->where('is_global', true)
            ->where(function ($q) use ($query) {
                $q->where('brand_name', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->orWhere('strength', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'brand_name', 'generic_name', 'strength', 'category_id')
            ->take(15)
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'brand_name'    => $m->brand_name ?? $m->name,
                'name'          => $m->name,
                'generic_name'  => $m->generic_name ?? '',
                'strength'      => $m->strength ?? '',
                'category_name' => $m->category?->name ?? '',
                'display'       => trim(($m->brand_name ?? $m->name) . ($m->strength && !str_contains(($m->brand_name ?? $m->name), $m->strength) ? ' ' . $m->strength : '')),
            ]);

        return response()->json($medicines);
    }

    public function quickSuggest(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'strength' => 'nullable|string|max:100',
            'generic_name' => 'nullable|string|max:255',
        ]);

        $name = trim($request->input('name'));
        $strength = $request->input('strength');
        $genericName = $request->input('generic_name');
        $normalizedName = mb_strtolower($name);

        $existingSuggestion = MedicineSuggestion::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->whereRaw('LOWER(COALESCE(strength, \'\')) = ?', [mb_strtolower(trim($strength ?? ''))])
            ->where('doctor_id', auth()->id())
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingSuggestion) {
            return response()->json([
                'success' => true,
                'message' => 'Suggestion already exists with status: ' . ucfirst($existingSuggestion->status),
                'suggestion_id' => $existingSuggestion->id,
                'status' => $existingSuggestion->status,
            ]);
        }

        $duplicateMedicine = Medicine::findDuplicate($name, $strength, $genericName);

        if ($duplicateMedicine) {
            return response()->json([
                'success' => false,
                'message' => 'This medicine already exists.',
                'medicine_id' => $duplicateMedicine->id,
            ]);
        }

        $suggestion = MedicineSuggestion::create([
            'name' => $name,
            'strength' => $strength,
            'generic_name' => $genericName,
            'doctor_id' => auth()->id(),
            'status' => MedicineSuggestion::STATUS_PENDING,
            'reason' => 'Quick suggested from prescription form',
        ]);

        User::role('admin')->each(fn ($admin) => $admin->notify(new MedicineSuggestionCreated($suggestion)));

        return response()->json([
            'success' => true,
            'message' => 'Medicine suggested successfully. Admin will review it.',
            'suggestion_id' => $suggestion->id,
        ]);
    }
}
