<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
            'role' => Role::Employee, // el Administrador asigna rol y departamento (RF-06, RF-07)
            'active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['current_session_id' => $request->session()->getId(), 'last_login_at' => now()])->saveQuietly();

        return redirect()->route('dashboard');
    }
}
