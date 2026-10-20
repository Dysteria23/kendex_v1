<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Anti-spam sin CAPTCHA: campo señuelo + tiempo mínimo de llenado (checklist 18). */
class Honeypot
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('kendex.honeypot') || ! $request->isMethod('post')) {
            return $next($request);
        }

        $filledTrap = filled($request->input('website'));

        try {
            $elapsed = now()->timestamp - (int) Crypt::decryptString((string) $request->input('kx_ts'));
        } catch (Throwable) {
            $elapsed = -1;
        }

        if ($filledTrap || $elapsed < 2 || $elapsed > 7200) {
            throw ValidationException::withMessages([
                'email' => 'No pudimos validar el envío. Recarga la página e inténtalo de nuevo.',
            ]);
        }

        return $next($request);
    }
}
