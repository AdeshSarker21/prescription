<?php

use App\Services\ModuleAccessService;
use App\Services\ModulePermissionService;
use App\Services\ModuleRegistry;

if (!function_exists('module')) {
    /**
     * Get the ModuleRegistry instance.
     */
    function module(): ModuleRegistry
    {
        return app(ModuleRegistry::class);
    }
}

if (!function_exists('module_access')) {
    /**
     * Get the ModuleAccessService instance.
     */
    function module_access(): ModuleAccessService
    {
        return app(ModuleAccessService::class);
    }
}

if (!function_exists('module_perm')) {
    /**
     * Get the ModulePermissionService instance.
     */
    function module_perm(): ModulePermissionService
    {
        return app(ModulePermissionService::class);
    }
}

if (!function_exists('can_access_module')) {
    /**
     * Check if the current user can access a module.
     */
    function can_access_module(string $moduleKey): bool
    {
        return module_access()->canAccess($moduleKey);
    }
}

if (!function_exists('is_module_enabled')) {
    /**
     * Check if a module is enabled for a specific doctor.
     */
    function is_module_enabled(string $moduleKey, int $doctorId): bool
    {
        return module_access()->isModuleEnabledForDoctor($moduleKey, $doctorId);
    }
}

if (!function_exists('has_module_permission')) {
    /**
     * Check if the current user has a specific permission for a module.
     */
    function has_module_permission(string $moduleSlug, string $permission): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return module_perm()->hasPermission($user, $moduleSlug, $permission);
    }
}

if (!function_exists('module_icon')) {
    /**
     * Get SVG icon for a module sidebar item.
     */
    function module_icon(string $iconKey): string
    {
        return module()->getIcon($iconKey);
    }
}
