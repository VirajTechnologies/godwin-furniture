<?php

namespace App\Http\Middleware;

use App\Support\PortalRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isSuperAdmin() || $user->status !== 'active') {
            if ($user !== null && $user->status === 'active' && ($user->isAgent() || $user->isBranchUser())) {
                return redirect()->to(PortalRedirect::homeFor($user));
            }

            abort(403);
        }

        return $next($request);
    }
}
