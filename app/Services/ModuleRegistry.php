<?php

namespace App\Services;

use App\Models\DoctorFeatureSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ModuleRegistry
{
    protected array $modules = [];
    protected array $resolved = [];

    public function __construct()
    {
        $this->modules = config('modules.modules', []);
    }

    /**
     * Get all registered modules.
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Get a single module by key.
     */
    public function get(string $key): ?array
    {
        return $this->modules[$key] ?? null;
    }

    /**
     * Check if a module key is registered.
     */
    public function has(string $key): bool
    {
        return isset($this->modules[$key]);
    }

    /**
     * Get all enabled modules.
     */
    public function enabled(): array
    {
        return array_filter($this->modules, fn ($m) => $m['enabled'] ?? true);
    }

    /**
     * Get all core modules.
     */
    public function core(): array
    {
        return array_filter($this->modules, fn ($m) => $m['core'] ?? false);
    }

    /**
     * Get all optional (non-core) modules.
     */
    public function optional(): array
    {
        return array_filter($this->modules, fn ($m) => !($m['core'] ?? false));
    }

    /**
     * Check if a module is enabled globally.
     */
    public function isEnabled(string $key): bool
    {
        $module = $this->get($key);
        return $module && ($module['enabled'] ?? true);
    }

    /**
     * Check if a module is core (always on).
     */
    public function isCore(string $key): bool
    {
        $module = $this->get($key);
        return $module && ($module['core'] ?? false);
    }

    /**
     * Check if a user can access a specific module.
     * Checks: 1) module exists, 2) module enabled, 3) plan allows it, 4) doctor feature setting allows it.
     */
    public function canAccess(string $key, User $user): bool
    {
        $module = $this->get($key);

        if (!$module) {
            return false;
        }

        if (!$module['enabled'] ?? true) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($module['core'] ?? false) {
            return true;
        }

        $planKey = $module['plan_key'] ?? $key;
        if (!$user->hasFeature($planKey)) {
            return false;
        }

        if ($user->isDoctor() || $user->isAssistant()) {
            $setting = DoctorFeatureSetting::getForDoctor(
                $user->isDoctor() ? $user->id : $user->getAccessibleDoctorIds()[0] ?? $user->id
            );
            $column = 'module_' . $key;
            if (method_exists($setting, 'hasModule')) {
                return $setting->hasModule($key);
            }
            if (property_exists($setting, $column) || in_array($column, $setting->getFillable())) {
                return (bool) $setting->$column;
            }
        }

        return true;
    }

    /**
     * Get sidebar menu items for a role.
     */
    public function getSidebarItems(string $role): array
    {
        $items = [];

        foreach ($this->modules as $key => $module) {
            if (!($module['enabled'] ?? true)) {
                continue;
            }

            if (!isset($module['sidebar'][$role])) {
                continue;
            }

            $sidebar = $module['sidebar'][$role];
            $items[] = array_merge($sidebar, [
                'module_key' => $key,
                'module_name' => $module['name'],
                'is_core' => $module['core'] ?? false,
            ]);
        }

        usort($items, function ($a, $b) {
            $groupOrder = ['core' => 0, 'modules' => 1, 'tools' => 2, 'settings' => 3];
            $aGroup = $groupOrder[$a['group'] ?? 'core'] ?? 99;
            $bGroup = $groupOrder[$b['group'] ?? 'core'] ?? 99;
            if ($aGroup !== $bGroup) {
                return $aGroup <=> $bGroup;
            }
            return ($a['module_key'] ?? '') <=> ($b['module_key'] ?? '');
        });

        return $items;
    }

    /**
     * Get the SVG icon for a sidebar icon key.
     */
    public function getIcon(string $iconKey): string
    {
        $icons = config('modules.icons', []);
        return $icons[$iconKey] ?? $icons['cog'];
    }

    /**
     * Get module count stats.
     */
    public function getStats(): array
    {
        $all = $this->all();
        return [
            'total'   => count($all),
            'enabled' => count($this->enabled()),
            'core'    => count($this->core()),
            'optional' => count($this->optional()),
        ];
    }
}
