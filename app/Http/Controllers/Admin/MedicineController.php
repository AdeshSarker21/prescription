<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = $request->get('search');

        $medicines = Medicine::with(['category', 'creator'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('brand_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {
            $html = view('admin.medicines._rows', compact('medicines'))->render();
            $pagination = $medicines->appends(['search' => $search])->links()->toHtml();
            return response()->json(compact('html', 'pagination'));
        }

        return view('admin.medicines.index', compact('medicines', 'search'));
    }

    public function create(): View
    {
        $categories = MedicineCategory::where('is_active', true)->get();
        return view('admin.medicines.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'strength' => 'nullable|string|max:100',
            'active_ingredients' => 'nullable|string',
            'salt_composition' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'batch_required' => 'boolean',
            'adult_dose' => 'nullable|string|max:255',
            'child_dose' => 'nullable|string|max:255',
            'max_daily_dose' => 'nullable|string|max:255',
            'duration_recommendation' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'pregnancy_safe' => 'boolean',
            'allergy_warning' => 'nullable|string',
            'drug_interaction_notes' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'food_interaction' => 'nullable|string|max:100',
            'alcohol_warning' => 'boolean',
            'status' => 'required|in:active,pending,rejected',
        ]);

        $data['is_global'] = true;
        $data['created_by'] = $request->user()->id;

        Medicine::create($data);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['category', 'creator']);
        return view('admin.medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        $categories = MedicineCategory::where('is_active', true)->get();
        return view('admin.medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'strength' => 'nullable|string|max:100',
            'active_ingredients' => 'nullable|string',
            'salt_composition' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'batch_required' => 'boolean',
            'adult_dose' => 'nullable|string|max:255',
            'child_dose' => 'nullable|string|max:255',
            'max_daily_dose' => 'nullable|string|max:255',
            'duration_recommendation' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'pregnancy_safe' => 'boolean',
            'allergy_warning' => 'nullable|string',
            'drug_interaction_notes' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'food_interaction' => 'nullable|string|max:100',
            'alcohol_warning' => 'boolean',
            'status' => 'required|in:active,pending,rejected',
        ]);

        $medicine->update($data);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->delete();
        return redirect()->route('admin.medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }
}
