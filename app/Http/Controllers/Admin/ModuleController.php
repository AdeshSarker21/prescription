<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorFeatureSetting;
use App\Models\User;
use App\Services\ModuleAccessService;
use App\Services\ModuleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleRegistry $registry,
        protected ModuleAccessService $access,
    ) {}

    /**
     * Display all registered modules.
     */
    public function index()
    {
        $modules = $this->registry->all();
        $stats = $this->registry->getStats();

        $doctorsWithModules = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->with('doctorFeatureSetting')
            ->get()
            ->map(fn ($doctor) => [
                'doctor' => $doctor,
                'modules' => DoctorFeatureSetting::getForDoctor($doctor->id)->getEnabledModules(),
            ]);

        return view('admin.modules.index', compact('modules', 'stats', 'doctorsWithModules'));
    }

    /**
     * Display per-doctor module settings for a specific module.
     */
    public function doctorSettings(string $moduleKey)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->with('doctorFeatureSetting')
            ->get()
            ->map(function ($doctor) use ($moduleKey) {
                $setting = DoctorFeatureSetting::getForDoctor($doctor->id);
                return [
                    'doctor' => $doctor,
                    'enabled' => $setting->hasModule($moduleKey),
                    'in_plan' => $doctor->hasFeature($module['plan_key'] ?? $moduleKey),
                ];
            });

        return view('admin.modules.doctor-settings', compact('module', 'moduleKey', 'doctors'));
    }

    /**
     * Toggle a module for a specific doctor.
     */
    public function updateDoctorModule(Request $request, string $moduleKey, int $doctorId)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $setting = DoctorFeatureSetting::getForDoctor($doctorId);
        $setting->setModule($moduleKey, (bool) $request->enabled);

        return back()->with('success', "Module '{$module['name']}' " . ($request->enabled ? 'enabled' : 'disabled') . " for doctor.");
    }
}
