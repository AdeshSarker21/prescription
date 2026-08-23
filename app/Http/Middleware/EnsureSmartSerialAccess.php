<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSmartSerialAccess
{
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => 'You must be logged in to access Smart Serial.',
                ], 401);
            }
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasModulePermission('smart_serial', $permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'permission_denied',
                    'message' => "You do not have the '{$permission}' permission for Smart Serial.",
                ], 403);
            }
            abort(403, "You do not have permission to access Smart Serial.");
        }

        return $next($request);
    }
}
