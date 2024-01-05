<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;


class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $role = explode(',', $role);
        return $request->user()->role->has_perm($role)
            ? $next($request)
            :  abort(403,"You do not have permissions to access this resource");
    }
}
