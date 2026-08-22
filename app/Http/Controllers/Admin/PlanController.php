<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'description' => 'nullable|string|max:1000',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'semi_annual_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'lifetime_price' => 'required|numeric|min:0',
            'max_patients' => 'nullable|integer|min:1',
            'features' => 'nullable|string',
            'limitations' => 'nullable|string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data['features'] = $data['features'] ? array_map('trim', explode("\n", $data['features'])) : [];
        $data['limitations'] = $data['limitations'] ? array_map('trim', explode("\n", $data['limitations'])) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');

        Plan::create($data);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string|max:1000',
            'monthly_price' => 'required|numeric|min:0',
            'quarterly_price' => 'required|numeric|min:0',
            'semi_annual_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'lifetime_price' => 'required|numeric|min:0',
            'max_patients' => 'nullable|integer|min:1',
            'features' => 'nullable|string',
            'limitations' => 'nullable|string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data['features'] = $data['features'] ? array_map('trim', explode("\n", $data['features'])) : [];
        $data['limitations'] = $data['limitations'] ? array_map('trim', explode("\n", $data['limitations'])) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');

        $plan->update($data);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete a plan that has active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
