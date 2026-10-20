<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * RN-11: un usuario desactivado no puede seguir usando el sistema.
 * RF-08: solo se permite una sesión activa; iniciar en otro dispositivo revoca la anterior.
 */
class EnforceSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $revoked = ! $user->active
                || ($user->current_session_id && $user->current_session_id !== $request->session()->getId());

            if ($revoked) {
                AuditLogger::log($user->active ? 'session.revoked' : 'session.deactivated', 'User', $user->id, [], $user->id);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => $user->active
                        ? 'Tu sesión se cerró porque iniciaste sesión en otro dispositivo.'
                        : 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }
        }

        return $next($request);
    }
}
