<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\LoginRequest;
use App\Http\Requests\Store\RegisterRequest;
use App\Support\StoreCustomerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('store.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->credentials(), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user === null || ! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Please contact the store.',
            ]);
        }

        try {
            app(StoreCustomerAccount::class)->ensureFor($user);
        } catch (RuntimeException $exception) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => $exception->getMessage(),
            ]);
        }

        return redirect()->intended(route('store.home'));
    }

    public function showRegister(): View
    {
        return view('store.register');
    }

    public function register(RegisterRequest $request, StoreCustomerAccount $accounts): RedirectResponse
    {
        try {
            $user = $accounts->register($request->registration());
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'phone' => $exception->getMessage(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('store.home'))
            ->with('success', 'Welcome to Godwin. Your account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.home');
    }
}
