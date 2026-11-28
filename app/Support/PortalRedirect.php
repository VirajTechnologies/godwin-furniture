<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PortalRedirect
{
    public static function homeFor(?User $user): string
    {
        if ($user?->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        if ($user?->isBranchUser()) {
            return route('branch.home');
        }

        if ($user?->isAgent()) {
            return route('agent.home');
        }

        return route('store.home');
    }

    public static function afterLogin(Request $request, string $portalPrefix, string $home): RedirectResponse
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && self::isWithinPortal($intended, $portalPrefix)) {
            return redirect()->to($intended);
        }

        return redirect()->to($home);
    }

    private static function isWithinPortal(string $url, string $portalPrefix): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $base = rtrim(parse_url(url($portalPrefix), PHP_URL_PATH) ?? ('/'.trim($portalPrefix, '/')), '/');

        return $path === $base || str_starts_with($path, $base.'/');
    }
}
