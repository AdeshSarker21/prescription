<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PackageModule;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::with('includedModules')->orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        $modules = Module::orderBy('sort_order')->get();

        return view('admin.plans.create', compact('modules'));
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
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
            'modules' => 'nullable|array',
            'modules.*' => 'string|exists:modules,slug',
            'ai_assistant_level' => 'nullable|string|in:off,basic,advanced',
            'analytics_enabled' => 'boolean',
            'multi_doctor_enabled' => 'boolean',
        ]);

        $data['features'] = $data['features'] ? array_map('trim', explode("\n", $data['features'])) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');

        // Build limitations as associative array
        $limitations = [
            'max_patients' => $data['max_patients'] ?? null,
            'ai_assistant' => $data['ai_assistant_level'] ?? false,
            'analytics' => $request->boolean('analytics_enabled'),
            'multi_doctor' => $request->boolean('multi_doctor_enabled'),
        ];

        // All available module slugs
        $allModuleSlugs = [
            'prescription', 'patient_management', 'appointment',
            'smart_serial', 'sms_notification', 'reports_analytics',
        ];

        $selectedModules = $data['modules'] ?? [];

        foreach ($allModuleSlugs as $slug) {
            $limitations[$slug] = in_array($slug, $selectedModules);
        }

        $data['limitations'] = $limitations;

        unset($data['modules'], $data['ai_assistant_level'], $data['analytics_enabled'], $data['multi_doctor_enabled']);

        $plan = Plan::create($data);

        // Sync package_modules pivot table
        $this->syncPlanModules($plan, $selectedModules);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        $modules = Module::orderBy('sort_order')->get();
        $planModules = $plan->includedModules->pluck('slug')->toArray();

        return view('admin.plans.edit', compact('plan', 'modules', 'planModules'));
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
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
            'modules' => 'nullable|array',
            'modules.*' => 'string|exists:modules,slug',
            'ai_assistant_level' => 'nullable|string|in:off,basic,advanced',
            'analytics_enabled' => 'boolean',
            'multi_doctor_enabled' => 'boolean',
        ]);

        $data['features'] = $data['features'] ? array_map('trim', explode("\n", $data['features'])) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_popular'] = $request->boolean('is_popular');

        // Build limitations as associative array
        $limitations = [
            'max_patients' => $data['max_patients'] ?? null,
            'ai_assistant' => $data['ai_assistant_level'] ?? false,
            'analytics' => $request->boolean('analytics_enabled'),
            'multi_doctor' => $request->boolean('multi_doctor_enabled'),
        ];

        // All available module slugs
        $allModuleSlugs = [
            'prescription', 'patient_management', 'appointment',
            'smart_serial', 'sms_notification', 'reports_analytics',
        ];

        $selectedModules = $data['modules'] ?? [];

        foreach ($allModuleSlugs as $slug) {
            $limitations[$slug] = in_array($slug, $selectedModules);
        }

        $data['limitations'] = $limitations;

        unset($data['modules'], $data['ai_assistant_level'], $data['analytics_enabled'], $data['multi_doctor_enabled']);

        $plan->update($data);

        // Sync package_modules pivot table
        $this->syncPlanModules($plan, $selectedModules);

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

    /**
     * Sync modules in the package_modules pivot table.
     */
    protected function syncPlanModules(Plan $plan, array $selectedModuleSlugs): void
    {
        $modules = Module::all();

        foreach ($modules as $module) {
            $isIncluded = in_array($module->slug, $selectedModuleSlugs);

            PackageModule::updateOrCreate(
                ['plan_id' => $plan->id, 'module_id' => $module->id],
                [
                    'is_included' => $isIncluded,
                    'sort_order' => $module->sort_order,
                ]
            );
        }
    }
}
