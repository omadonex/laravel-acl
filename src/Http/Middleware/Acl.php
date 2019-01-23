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
        $privileges = array_key_exists('privileges', $actions) ? $actions['privileges'] : null;

        if (!$privileges || app('acl')->check($privileges)) {
            return $next($request);
        }

        abort(404);
    }
}
