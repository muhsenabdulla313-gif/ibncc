<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    // Usage: ->middleware('role:admin')  or  ->middleware('role:member,guest')
    public function handle(Request $request, Closure $next, string $roles)
    {
        $allowed = explode(',', $roles);
 
        if (! auth()->check() || ! in_array(auth()->user()->role->name, $allowed)) {
            abort(403);
        }
 
        return $next($request);
    }
}
