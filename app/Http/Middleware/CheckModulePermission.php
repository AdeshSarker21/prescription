<?php

namespace App\Http\Middleware;

use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    public function __construct(
        protected ModulePermissionService $permissionService,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleSlug, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$this->permissionService->hasPermission($user, $moduleSlug, $permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'permission_denied',
                    'message' => "You do not have the '{$permission}' permission for this module.",
                ], 403);
            }

            return redirect()->back()
                ->with('error', "You do not have permission to perform this action.");
        }

        return $next($request);
    }
}
