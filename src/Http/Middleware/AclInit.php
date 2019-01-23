<?php

namespace Omadonex\LaravelAcl\Http\Middleware;

use Closure;

class AclInit {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if ($request->user()) {
            app('acl')->setUser($request->user());
        }

        return $next($request);
    }
}
