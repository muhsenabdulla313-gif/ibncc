<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    // Usage: ->middleware('role:admin')  or  ->middleware('role:member,guest')
    // public function handle(Request $request, Closure $next, string $roles)
    // {
    //     $allowed = explode(',', $roles);
 
    //     if (! auth()->check() || ! in_array(auth()->user()->role->name, $allowed)) {
    //         abort(403);
    //     }
 
    //     return $next($request);
    // }

     public function handle(Request $request, Closure $next, string $roles): Response
    {
        $allowed = array_map('trim', explode(',', $roles));

        // admin routes use the admin guard, everything else uses web
        $guard = in_array('admin', $allowed, true) ? 'admin' : 'web';

        $user = Auth::guard($guard)->user();

        if (! $user || ! in_array($user->role?->name, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
