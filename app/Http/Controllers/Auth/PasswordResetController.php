<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** RF-02: recuperación de contraseña por correo. */
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        PasswordBroker::sendResetLink($request->only('email'));

        // Misma respuesta exista o no el correo, para no revelar qué cuentas existen.
        return back()->with('status', 'Si el correo está registrado, te enviamos un enlace para restablecer la contraseña.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'failed_attempts' => 0,
                    'locked_until' => null,
                    'current_session_id' => null,
                ])->saveQuietly();

                AuditLogger::log('password.reset', 'User', $user->id, [], $user->id);
                event(new PasswordReset($user));
            }
        );

        return $status === PasswordBroker::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Contraseña actualizada. Ya puedes iniciar sesión.')
            : back()->withInput($request->only('email'))->withErrors(['email' => trans($status)]);
    }
}
