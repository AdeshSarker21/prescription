<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorFeatureSetting;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModule;
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
     * Display all registered modules with status dashboard.
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

        $dbModules = Module::withCount('users')->get()->keyBy('slug');

        return view('admin.modules.index', compact('modules', 'stats', 'doctorsWithModules', 'dbModules'));
    }

    /**
     * Toggle a module globally (enable/disable via DB).
     */
    public function toggleGlobal(Request $request, string $moduleKey)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $dbModule = Module::where('slug', $moduleKey)->first();
        if ($dbModule) {
            $dbModule->update(['is_active' => (bool) $request->enabled]);
        }

        $status = $request->enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Module '{$module['name']}' has been globally {$status}.");
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

    /**
     * Show user module assignment page.
     */
    public function userModules(string $moduleKey)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $dbModule = Module::where('slug', $moduleKey)->first();

        $users = User::with('roles')
            ->with(['userModules' => fn ($q) => $q->where('module_id', $dbModule?->id)])
            ->get()
            ->map(function ($user) use ($moduleKey, $dbModule) {
                $userModule = $user->userModules->first();
                return [
                    'user' => $user,
                    'is_enabled' => $userModule?->is_enabled ?? false,
                    'enabled_at' => $userModule?->enabled_at,
                    'notes' => $userModule?->notes,
                ];
            });

        $enabledCount = $users->where('is_enabled', true)->count();

        return view('admin.modules.user-modules', compact('module', 'moduleKey', 'dbModule', 'users', 'enabledCount'));
    }

    /**
     * Toggle a module for a specific user.
     */
    public function toggleUserModule(Request $request, string $moduleKey, int $userId)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $dbModule = Module::where('slug', $moduleKey)->first();

        if (!$dbModule) {
            return back()->with('error', 'Module not found in database. Please run the module seeder.');
        }

        $user = User::findOrFail($userId);
        $enabled = (bool) $request->enabled;

        UserModule::updateOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $dbModule->id,
            ],
            [
                'is_enabled' => $enabled,
                'enabled_by' => $enabled ? auth()->id() : null,
                'enabled_at' => $enabled ? now() : null,
                'disabled_at' => !$enabled ? now() : null,
            ]
        );

        $status = $enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Module '{$module['name']}' {$status} for {$user->name}.");
    }

    /**
     * Bulk toggle module for all doctors.
     */
    public function bulkToggleDoctorModule(Request $request, string $moduleKey)
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            abort(404, 'Module not found.');
        }

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->get();
        $enabled = (bool) $request->enabled;

        foreach ($doctors as $doctor) {
            $setting = DoctorFeatureSetting::getForDoctor($doctor->id);
            $setting->setModule($moduleKey, $enabled);
        }

        $status = $enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Module '{$module['name']}' {$status} for all {$doctors->count()} doctors.");
    }
}
