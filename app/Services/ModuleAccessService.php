<?php

namespace App\Services;

use App\Models\DoctorFeatureSetting;
use App\Models\User;

class ModuleAccessService
{
    protected ModuleRegistry $registry;

    public function __construct(ModuleRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Check if the current user can access a module.
     */
    public function canAccess(string $moduleKey, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        return $this->registry->canAccess($moduleKey, $user);
    }

    /**
     * Check if a module is enabled for a doctor (plan + feature setting).
     */
    public function isModuleEnabledForDoctor(string $moduleKey, int $doctorId): bool
    {
        $module = $this->registry->get($moduleKey);

        if (!$module || !($module['enabled'] ?? true)) {
            return false;
        }

        if ($module['core'] ?? false) {
            return true;
        }

        $planKey = $module['plan_key'] ?? $moduleKey;
        $doctor = User::find($doctorId);

        if (!$doctor) {
            return false;
        }

        if (!$doctor->hasFeature($planKey)) {
            return false;
        }

        $setting = DoctorFeatureSetting::getForDoctor($doctorId);
        return $setting->hasModule($moduleKey);
    }

    /**
     * Get all modules with their enabled/disabled status for a doctor.
     */
    public function getModulesStatusForDoctor(int $doctorId): array
    {
        $allModules = $this->registry->all();
        $result = [];

        foreach ($allModules as $key => $module) {
            $result[$key] = [
                'module' => $module,
                'enabled' => $this->isModuleEnabledForDoctor($key, $doctorId),
                'core' => $module['core'] ?? false,
            ];
        }

        return $result;
    }

    /**
     * Get modules that are available (not core, enabled globally) for a doctor's plan.
     */
    public function getAvailableModulesForDoctor(int $doctorId): array
    {
        $doctor = User::find($doctorId);

        if (!$doctor) {
            return [];
        }

        $allModules = $this->registry->all();
        $result = [];

        foreach ($allModules as $key => $module) {
            if ($module['core'] ?? false) {
                continue;
            }

            $planKey = $module['plan_key'] ?? $key;
            $inPlan = $doctor->hasFeature($planKey);

            $setting = DoctorFeatureSetting::getForDoctor($doctorId);
            $enabled = $setting->hasModule($key);

            $result[$key] = [
                'module' => $module,
                'in_plan' => $inPlan,
                'enabled' => $enabled,
            ];
        }

        return $result;
    }

    /**
     * Toggle a module for a doctor.
     */
    public function toggleModuleForDoctor(string $moduleKey, int $doctorId, bool $enabled): bool
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            return false;
        }

        $setting = DoctorFeatureSetting::getForDoctor($doctorId);
        $column = 'module_' . $moduleKey;

        if (method_exists($setting, 'setModule')) {
            $setting->setModule($moduleKey, $enabled);
            return true;
        }

        return false;
    }

    /**
     * Check if a module requires subscription (is not free/core).
     */
    public function requiresSubscription(string $moduleKey): bool
    {
        $module = $this->registry->get($moduleKey);

        if (!$module) {
            return false;
        }

        return !($module['core'] ?? false);
    }
}
