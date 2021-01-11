<?php

namespace Omadonex\LaravelAcl\Http\Middleware;

use Closure;

class Acl {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $actions = $request->route()->getAction();
        $permissions = array_key_exists('permissions', $actions) ? $actions['permissions'] : null;

        if (!$permissions || app('acl')->check($permissions)) {
            return $next($request);
        }

        abort(404);
    }
}
