<?php

namespace Omadonex\LaravelAcl\Http\Middleware;

use Closure;

class AclRole {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $actions = $request->route()->getAction();
        $roles = array_key_exists('roles', $actions) ? $actions['roles'] : null;

        if (!$roles || app('acl')->checkRoles($roles)) {
            return $next($request);
        }

        abort(404);
    }
}
