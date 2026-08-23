<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModulePermission;
use App\Models\User;
use App\Services\ModulePermissionService;
use Illuminate\Http\Request;

class ModulePermissionController extends Controller
{
    public function __construct(
        protected ModulePermissionService $permissionService,
    ) {}

    /**
     * Show permission management page for a module.
     */
    public function index(string $moduleSlug)
    {
        $module = Module::where('slug', $moduleSlug)->firstOrFail();
        $permissions = $this->permissionService->getModulePermissions($moduleSlug);
        $doctorsPermissions = $this->permissionService->getDoctorsPermissions($moduleSlug);

        return view('admin.modules.permissions', compact('module', 'moduleSlug', 'permissions', 'doctorsPermissions'));
    }

    /**
     * Update permissions for a specific doctor.
     */
    public function update(Request $request, string $moduleSlug, int $doctorId)
    {
        $module = Module::where('slug', $moduleSlug)->firstOrFail();

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        $doctor = User::findOrFail($doctorId);

        $this->permissionService->syncPermissions(
            $doctor,
            $moduleSlug,
            $request->permissions,
            $request->user()->id
        );

        return back()->with('success', "Permissions updated for Dr. {$doctor->name}.");
    }

    /**
     * Grant a single permission to a doctor.
     */
    public function grant(string $moduleSlug, int $doctorId, string $permissionName)
    {
        $doctor = User::findOrFail($doctorId);

        $this->permissionService->grantPermission(
            $doctor,
            $moduleSlug,
            $permissionName,
            request()->user()->id
        );

        return back()->with('success', "Permission '{$permissionName}' granted to Dr. {$doctor->name}.");
    }

    /**
     * Revoke a single permission from a doctor.
     */
    public function revoke(string $moduleSlug, int $doctorId, string $permissionName)
    {
        $doctor = User::findOrFail($doctorId);

        $this->permissionService->revokePermission($doctor, $moduleSlug, $permissionName);

        return back()->with('success', "Permission '{$permissionName}' revoked from Dr. {$doctor->name}.");
    }

    /**
     * Bulk toggle all permissions for a doctor.
     */
    public function toggleAll(Request $request, string $moduleSlug, int $doctorId)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $module = Module::where('slug', $moduleSlug)->firstOrFail();
        $doctor = User::findOrFail($doctorId);
        $allEnabled = (bool) $request->enabled;

        if ($allEnabled) {
            $allPermissions = $this->permissionService->getModulePermissions($moduleSlug)
                ->pluck('name')
                ->toArray();
        } else {
            $allPermissions = [];
        }

        $this->permissionService->syncPermissions(
            $doctor,
            $moduleSlug,
            $allPermissions,
            $request->user()->id
        );

        $status = $allEnabled ? 'granted' : 'revoked';
        return back()->with('success', "All permissions {$status} for Dr. {$doctor->name}.");
    }
}
