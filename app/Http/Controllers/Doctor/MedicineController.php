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

        $medicines = Medicine::where('status', 'active')
            ->where('is_global', true)
            ->where(function ($q) use ($query) {
                $q->where('brand_name', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('generic_name', 'like', "%{$query}%")
                    ->orWhere('strength', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'brand_name', 'generic_name', 'strength')
            ->take(15)
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'brand_name'   => $m->brand_name ?? $m->name,
                'name'         => $m->name,
                'generic_name' => $m->generic_name ?? '',
                'strength'     => $m->strength ?? '',
                'display'      => trim(($m->brand_name ?? $m->name) . ($m->strength && !str_contains(($m->brand_name ?? $m->name), $m->strength) ? ' ' . $m->strength : '')),
            ]);

        return response()->json($medicines);
    }
}
