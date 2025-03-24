<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasRole('superadmin')) {
            return response()->json(['message' => 'ليس لديك الصلاحية.'], 403);
        }

        return $next($request);
    }
}
