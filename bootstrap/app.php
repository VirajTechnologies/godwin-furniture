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
            return $request->is('branch', 'branch/*')
                ? route('branch.login')
                : route('admin.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->isBranchUser()
                ? route('branch.home')
                : route('admin.dashboard');
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

            $branchRequest = $request->is('branch', 'branch/*');
            $home = $branchRequest ? route('branch.home') : route('admin.dashboard');

            $redirect = auth()->check()
                ? redirect()->back(fallback: $home)
                : redirect()->route($branchRequest ? 'branch.login' : 'admin.login');

            return $redirect
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', auth()->check()
                    ? 'This page expired. Please try again.'
                    : 'Your session expired. Please sign in again.');
        });
    })->create();
