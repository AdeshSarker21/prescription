<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModulePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Support\Facades\Cache;

class ModulePermissionService
{
    /**
     * Check if a user has a specific permission for a module.
     */
    public function hasPermission(User $user, string $moduleSlug, string $permissionName): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $cacheKey = "module_perm_{$user->id}_{$moduleSlug}_{$permissionName}";

        return Cache::remember($cacheKey, 300, function () use ($user, $moduleSlug, $permissionName) {
            $module = Module::where('slug', $moduleSlug)->first();

            if (!$module) {
                return false;
            }

            if (!$module->is_active) {
                return false;
            }

            if ($module->is_core && !in_array($permissionName, ['view'])) {
                return true;
            }

            if (!$module->isEnabledForUser($user->id)) {
                return false;
            }

            $permission = ModulePermission::where('module_id', $module->id)
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if (!$permission) {
                return false;
            }

            return UserModulePermission::where('user_id', $user->id)
                ->where('module_permission_id', $permission->id)
                ->where('is_granted', true)
                ->exists();
        });
    }

    /**
     * Check if a user has ALL of the given permissions for a module.
     */
    public function hasAllPermissions(User $user, string $moduleSlug, array $permissionNames): bool
    {
        foreach ($permissionNames as $name) {
            if (!$this->hasPermission($user, $moduleSlug, $name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a user has ANY of the given permissions for a module.
     */
    public function hasAnyPermission(User $user, string $moduleSlug, array $permissionNames): bool
    {
        foreach ($permissionNames as $name) {
            if ($this->hasPermission($user, $moduleSlug, $name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all permissions for a module.
     */
    public function getModulePermissions(string $moduleSlug): \Illuminate\Database\Eloquent\Collection
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return collect();
        }

        return ModulePermission::where('module_id', $module->id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all permissions granted to a user for a module.
     */
    public function getUserPermissions(User $user, string $moduleSlug): array
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return [];
        }

        if ($user->isAdmin()) {
            return $this->getModulePermissions($moduleSlug)
                ->pluck('name')
                ->toArray();
        }

        return UserModulePermission::where('user_id', $user->id)
            ->where('is_granted', true)
            ->whereHas('modulePermission', fn ($q) => $q->where('module_id', $module->id))
            ->join('module_permissions', 'user_module_permissions.module_permission_id', '=', 'module_permissions.id')
            ->pluck('module_permissions.name')
            ->toArray();
    }

    /**
     * Get permission status for all doctors for a module.
     */
    public function getDoctorsPermissions(string $moduleSlug): array
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return [];
        }

        $permissions = ModulePermission::where('module_id', $module->id)
            ->orderBy('name')
            ->get();

        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->get();

        $result = [];
        foreach ($doctors as $doctor) {
            $grantedPermissions = UserModulePermission::where('user_id', $doctor->id)
                ->where('is_granted', true)
                ->whereHas('modulePermission', fn ($q) => $q->where('module_id', $module->id))
                ->join('module_permissions', 'user_module_permissions.module_permission_id', '=', 'module_permissions.id')
                ->pluck('module_permissions.name')
                ->toArray();

            $result[] = [
                'doctor' => $doctor,
                'module_enabled' => $module->isEnabledForUser($doctor->id),
                'permissions' => $permissions->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'is_granted' => in_array($p->name, $grantedPermissions),
                ])->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Grant a permission to a user.
     */
    public function grantPermission(User $user, string $moduleSlug, string $permissionName, ?int $grantedBy = null): bool
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return false;
        }

        $permission = ModulePermission::where('module_id', $module->id)
            ->where('name', $permissionName)
            ->where('guard_name', 'web')
            ->first();

        if (!$permission) {
            return false;
        }

        UserModulePermission::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_permission_id' => $permission->id,
            ],
            [
                'is_granted' => true,
                'granted_by' => $grantedBy ? (string) $grantedBy : null,
                'granted_at' => now(),
            ]
        );

        $this->clearPermissionCache($user->id, $moduleSlug);

        return true;
    }

    /**
     * Revoke a permission from a user.
     */
    public function revokePermission(User $user, string $moduleSlug, string $permissionName): bool
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return false;
        }

        $permission = ModulePermission::where('module_id', $module->id)
            ->where('name', $permissionName)
            ->where('guard_name', 'web')
            ->first();

        if (!$permission) {
            return false;
        }

        UserModulePermission::where('user_id', $user->id)
            ->where('module_permission_id', $permission->id)
            ->update(['is_granted' => false]);

        $this->clearPermissionCache($user->id, $moduleSlug);

        return true;
    }

    /**
     * Bulk update all permissions for a user on a module.
     */
    public function syncPermissions(User $user, string $moduleSlug, array $permissionNames, ?int $grantedBy = null): void
    {
        $module = Module::where('slug', $moduleSlug)->first();

        if (!$module) {
            return;
        }

        $permissions = ModulePermission::where('module_id', $module->id)->get();

        foreach ($permissions as $perm) {
            $isGranted = in_array($perm->name, $permissionNames);

            UserModulePermission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_permission_id' => $perm->id,
                ],
                [
                    'is_granted' => $isGranted,
                    'granted_by' => $isGranted && $grantedBy ? (string) $grantedBy : null,
                    'granted_at' => $isGranted ? now() : null,
                ]
            );
        }

        $this->clearPermissionCache($user->id, $moduleSlug);
    }

    /**
     * Clear permission cache for a user.
     */
    public function clearPermissionCache(int $userId, ?string $moduleSlug = null): void
    {
        if ($moduleSlug) {
            $module = Module::where('slug', $moduleSlug)->first();
            if ($module) {
                $permissions = ModulePermission::where('module_id', $module->id)->get();
                foreach ($permissions as $perm) {
                    Cache::forget("module_perm_{$userId}_{$moduleSlug}_{$perm->name}");
                }
            }
        } else {
            $modules = Module::all();
            foreach ($modules as $mod) {
                $permissions = ModulePermission::where('module_id', $mod->id)->get();
                foreach ($permissions as $perm) {
                    Cache::forget("module_perm_{$userId}_{$mod->slug}_{$perm->name}");
                }
            }
        }
    }
}
