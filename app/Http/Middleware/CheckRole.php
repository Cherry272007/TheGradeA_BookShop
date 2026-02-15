<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * * @param Request $request
     * @param Closure $next
     * @param string $role  <-- This allows the role to be passed from the route
     */
    public function handle(Request $request, Closure $next, string $role): Response
{
    // 1. Check if user is logged in
    // 2. Check if their role matches the parameter passed from the route
    if (!$request->user() || $request->user()->role !== $role) {
        return response()->json([
            'status' => 'error',
            'message' => "Unauthorized. You do not have {$role} access."
        ], 403);
    }

    return $next($request);
}
}