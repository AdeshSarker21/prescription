<?php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use App\Services\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    public function __construct(
        protected ModuleRegistry $registry,
        protected ModuleAccessService $access,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $module = $this->registry->get($moduleKey);

        if (!$module) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'module_not_found',
                    'message' => "Module '{$moduleKey}' is not registered.",
                ], 404);
            }
            abort(404, "Module not found.");
        }

        if (!($module['enabled'] ?? true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'module_disabled',
                    'message' => "The '{$module['name']}' module is currently disabled.",
                ], 403);
            }
            return redirect()->route('admin.dashboard')
                ->with('error', "The '{$module['name']}' module is currently disabled.");
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($module['core'] ?? false) {
            return $next($request);
        }

        if (!$this->access->canAccess($moduleKey, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'module_access_denied',
                    'message' => "You do not have access to the '{$module['name']}' module. Please upgrade your plan or ask your administrator to enable it.",
                ], 403);
            }
            return redirect()->route('doctor.subscription.plans')
                ->with('error', "Your current plan does not include the '{$module['name']}' module. Please upgrade.");
        }

        return $next($request);
    }
}
