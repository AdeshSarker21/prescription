<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MedicineCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MedicineCategory::withCount('medicines')->latest()->paginate(20);
        return view('admin.medicines.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name',
            'description' => 'nullable|string|max:500',
        ]);
        $data['slug'] = Str::slug($data['name']);

        MedicineCategory::create($data);

        return redirect()->route('admin.medicines.categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(MedicineCategory $category): View
    {
        return view('admin.medicines.category_edit', compact('category'));
    }

    public function update(Request $request, MedicineCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return redirect()->route('admin.medicines.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(MedicineCategory $category): RedirectResponse
    {
        if ($category->medicines()->count() > 0) {
            return back()->with('error', 'Cannot delete category with linked medicines.');
        }
        $category->delete();
        return redirect()->route('admin.medicines.categories.index')
            ->with('success', 'Category deleted.');
    }
}
