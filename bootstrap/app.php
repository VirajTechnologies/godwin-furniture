<?php

use App\Http\Middleware\EnsureBranchUser;
use App\Http\Middleware\EnsureSuperAdmin;
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
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('branch', 'branch/*')) {
                return route('branch.login');
            }

            if ($request->is('admin', 'admin/*')) {
                return route('admin.login');
            }

            return route('store.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                return route('admin.dashboard');
            }

            if ($request->is('branch', 'branch/*')) {
                return route('branch.home');
            }

            return route('store.home');
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

            if ($request->is('branch', 'branch/*')) {
                $home = route('branch.home');
                $login = 'branch.login';
            } elseif ($request->is('admin', 'admin/*')) {
                $home = route('admin.dashboard');
                $login = 'admin.login';
            } else {
                $home = route('store.home');
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
