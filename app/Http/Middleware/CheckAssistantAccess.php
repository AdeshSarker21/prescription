<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAssistantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAssistant()) {
            return redirect()->route('login');
        }

        $doctorIds = $user->getAccessibleDoctorIds();

        if (empty($doctorIds)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'access_denied',
                    'message' => 'You are not assigned to any doctor. Please contact admin.',
                ], 403);
            }

            return redirect()->route('assistant.dashboard')
                ->with('error', 'You are not assigned to any doctor. Please contact admin.');
        }

        $request->merge(['_accessible_doctor_ids' => $doctorIds]);

        return $next($request);
    }
}
