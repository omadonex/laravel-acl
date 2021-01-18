<?php

namespace Omadonex\LaravelAcl\Http\Middleware;

use Closure;
use Omadonex\LaravelAcl\Interfaces\IAclService;

class AclRole {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        /** @var IAclService $aclService */
        $aclService = app(IAclService::class);

        $actions = $request->route()->getAction();
        $roles = $actions['roles'] ?? [];

        if (!$roles || $aclService->checkRole($roles)) {
            return $next($request);
        }

        abort(404);
    }
}
