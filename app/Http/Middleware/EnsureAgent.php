<?php

namespace App\Http\Middleware;

use App\Models\Agent;
use App\Support\PortalRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user?->loadMissing('agent');
        $agent = $user?->agent;

        if ($user === null
            || ! $user->isAgent()
            || $user->status !== 'active'
            || ! $agent instanceof Agent
            || ! $agent->isActive()) {
            if ($user !== null && $user->status === 'active' && ($user->isSuperAdmin() || $user->isBranchUser())) {
                return redirect()->to(PortalRedirect::homeFor($user));
            }

            abort(403);
        }

        return $next($request);
    }
}
