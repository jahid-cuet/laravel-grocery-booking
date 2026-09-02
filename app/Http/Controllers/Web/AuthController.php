<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Show the web login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a web login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->load('role');

        // Store JWT token in session for seamless AJAX fetch authorization
        $jwtToken = JWTAuth::fromUser($user);
        $request->session()->put('jwt_token', $jwtToken);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.groceries.index'))
                ->with('success', "Welcome back, Admin {$user->name}!");
        }

        return redirect()->intended(route('store.index'))
            ->with('success', "Welcome back, {$user->name}!");
    }

    /**
     * Handle quick 1-click demo login for evaluators.
     */
    public function quickLogin(Request $request, string $role): RedirectResponse
    {
        $email = $role === 'admin' ? 'admin@grocery.com' : 'user@grocery.com';
        $user = User::where('email', $email)->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        $jwtToken = JWTAuth::fromUser($user);
        $request->session()->put('jwt_token', $jwtToken);

        if ($user->isAdmin()) {
            return redirect()->route('admin.groceries.index')
                ->with('success', "Logged in as Admin ({$user->name})");
        }

        return redirect()->route('store.index')
            ->with('success', "Logged in as Customer ({$user->name})");
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('store.index')
            ->with('info', 'You have been logged out.');
    }
}
