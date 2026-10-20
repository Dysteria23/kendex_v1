<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // RNF-04: máximo 5 intentos fallidos cada 15 minutos por IP.
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => (int) ceil($seconds / 60)]),
            ]);
        }

        $user = User::where('email', Str::lower($request->input('email')))->first();

        // RF-05: bloqueo de cuenta tras N intentos fallidos.
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            RateLimiter::hit($key, 900);
            AuditLogger::log('login.blocked', 'User', $user->id, [], $user->id);

            throw ValidationException::withMessages([
                'email' => 'La cuenta está bloqueada temporalmente por demasiados intentos. Inténtalo de nuevo en unos minutos.',
            ]);
        }

        if (! $user || ! $user->active || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($key, 900);

            if ($user) {
                $user->failed_attempts++;
                if ($user->failed_attempts >= (int) Settings::get('max_failed_attempts')) {
                    $user->locked_until = now()->addMinutes((int) Settings::get('lockout_minutes'));
                    $user->failed_attempts = 0;
                }
                $user->saveQuietly();
            }

            AuditLogger::log('login.failed', 'User', $user?->id, ['email' => $request->input('email')], $user?->id);

            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($key);

        // Sin "recordarme": una sola sesión activa por usuario (RF-08).
        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'current_session_id' => $request->session()->getId(),
        ])->saveQuietly();

        AuditLogger::log('login', 'User', $user->id, [], $user->id);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user) {
            AuditLogger::log('logout', 'User', $user->id, [], $user->id);
            $user->forceFill(['current_session_id' => null])->saveQuietly();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
