<?php

use App\Http\Middleware\EnsureAgent;
use App\Http\Middleware\EnsureBranchUser;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Support\PortalRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => EnsureSuperAdmin::class,
            'branch_user' => EnsureBranchUser::class,
            'agent' => EnsureAgent::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('branch', 'branch/*')) {
                return route('branch.login');
            }

            if ($request->is('agent', 'agent/*')) {
                return route('agent.login');
            }

            if ($request->is('admin', 'admin/*')) {
                return route('admin.login');
            }

            return route('store.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            return PortalRedirect::homeFor($request->user());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Refresh the page and try again.',
                ], 419);
            }

            $home = PortalRedirect::homeFor($request->user());

            if ($request->is('branch', 'branch/*')) {
                $login = 'branch.login';
            } elseif ($request->is('agent', 'agent/*')) {
                $login = 'agent.login';
            } elseif ($request->is('admin', 'admin/*')) {
                $login = 'admin.login';
            } else {
                $login = 'store.login';
            }

            $redirect = auth()->check()
                ? redirect()->back(fallback: $home)
                : redirect()->route($login);

            return $redirect
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', auth()->check()
                    ? 'This page expired. Please try again.'
                    : 'Your session expired. Please sign in again.');
        });
    })->create();
