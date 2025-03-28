<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!$request->user() || !$request->user()->can($permission)) {
            return response()->json(['message' => 'ليس لديك الإذن للقيام بهذا الإجراء'], 403);
        }
        return $next($request);
    }
}
