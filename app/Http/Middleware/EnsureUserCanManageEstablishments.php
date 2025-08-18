<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageEstablishments
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canManageEstablishments()) {
            return response()->json([
                'error' => 'Vous n\'avez pas les permissions pour effectuer cette action.'
            ], 403);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'error' => 'Votre compte est désactivé.'
            ], 403);
        }

        return $next($request);
    }
}
