<?php

namespace Omadonex\LaravelAcl\Http\Middleware;

use Closure;
use Omadonex\LaravelAcl\Interfaces\IAclService;

class Acl {
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
        $permissions = $actions['permissions'] ?? null;

        if (!$permissions || $aclService->check($permissions)) {
            return $next($request);
        }

        abort(404);
    }
}
