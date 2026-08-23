<?php

namespace App\Providers;

use App\Services\ModuleAccessService;
use App\Services\ModulePermissionService;
use App\Services\ModuleRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, function () {
            return new ModuleRegistry();
        });

        $this->app->singleton(ModuleAccessService::class, function ($app) {
            return new ModuleAccessService($app->make(ModuleRegistry::class));
        });

        $this->app->singleton(ModulePermissionService::class, function () {
            return new ModulePermissionService();
        });
    }

    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('module', function (string $moduleKey) {
            $access = app(ModuleAccessService::class);
            return $access->canAccess($moduleKey);
        });

        Blade::if('moduleEnabled', function (string $moduleKey, ?int $doctorId = null) {
            $access = app(ModuleAccessService::class);
            if ($doctorId) {
                return $access->isModuleEnabledForDoctor($moduleKey, $doctorId);
            }
            return $access->canAccess($moduleKey);
        });

        Blade::if('modulePermission', function (string $moduleSlug, string $permission) {
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            $service = app(ModulePermissionService::class);
            return $service->hasPermission($user, $moduleSlug, $permission);
        });
    }
}
