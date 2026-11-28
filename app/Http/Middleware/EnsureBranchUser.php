<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Support\PortalRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user?->loadMissing('employee.branch');
        $branch = $user?->employee?->branch;

        if ($user === null
            || ! $user->isBranchUser()
            || $user->status !== 'active'
            || ! $user->employee?->isActive()
            || ! $branch instanceof Branch
            || ! $branch->isActive()) {
            if ($user !== null && $user->status === 'active' && ($user->isSuperAdmin() || $user->isAgent())) {
                return redirect()->to(PortalRedirect::homeFor($user));
            }

            abort(403);
        }

        return $next($request);
    }
}
