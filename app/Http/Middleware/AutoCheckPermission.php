<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class AutoCheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();
        $permission = Permission::whereRaw("FIND_IN_SET(?, routes)", [$routeName])->first();
        if (!$permission) {
            return response()->json(['message' => 'Access denied. Route permission not found.'], 403);
        }
        if (!$request->user() || !$request->user()->can($permission->name)) {
            return response()->json(['message' => 'You do not have permission to access this route.'], 403);
        }
        return $next($request);
    }
}
