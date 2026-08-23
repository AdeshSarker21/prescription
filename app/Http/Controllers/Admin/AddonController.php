<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleAddon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(): View
    {
        $addons = ModuleAddon::with('module')->ordered()->get();

        return view('admin.addons.index', compact('addons'));
    }

    public function create(): View
    {
        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.addons.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:module_addons,slug',
            'description' => 'nullable|string|max:1000',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'semi_annual_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'lifetime_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        ModuleAddon::create($data);

        return redirect()->route('admin.addons.index')
            ->with('success', 'Add-on created successfully.');
    }

    public function edit(ModuleAddon $addon): View
    {
        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.addons.edit', compact('addon', 'modules'));
    }

    public function update(Request $request, ModuleAddon $addon): RedirectResponse
    {
        $data = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:module_addons,slug,' . $addon->id,
            'description' => 'nullable|string|max:1000',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'semi_annual_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'lifetime_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $addon->update($data);

        return redirect()->route('admin.addons.index')
            ->with('success', 'Add-on updated successfully.');
    }

    public function destroy(ModuleAddon $addon): RedirectResponse
    {
        if ($addon->activeSubscriptions()->exists()) {
            return back()->with('error', 'Cannot delete an add-on with active subscriptions.');
        }

        $addon->delete();

        return redirect()->route('admin.addons.index')
            ->with('success', 'Add-on deleted successfully.');
    }
}
