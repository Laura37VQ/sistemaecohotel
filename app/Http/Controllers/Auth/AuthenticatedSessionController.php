<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar el formulario de login
     */
    public function create(Request $request): Response
    {
        /** @var \Illuminate\Http\Request $request */
        return Inertia::render('auth/Login', [
            'canResetPassword' => route('password.request') ? true : false,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Procesar login
     */
    public function store(Request $request)
    {
        /** @var \Illuminate\Http\Request $request */
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required|string',
        ]);

        // Buscar usuario por correo
        $user = \App\Models\User::where('correo', $request->correo)->first();

        if (!$user || !Hash::check($request->contrasena, $user->contrasena)) {
            return back()->withErrors([
                'correo' => 'Credenciales incorrectas.',
            ])->onlyInput('correo');
        }

        // Loguear usuario
        Auth::login($user, $request->boolean('remember'));

        // Regenerar sesión
        $request->session()->regenerate();

        // Redirigir al dashboard
        return redirect()->intended('/dashboard');
    }

    /**
     * Logout
     */
    public function destroy(Request $request)
    {
        /** @var \Illuminate\Http\Request $request */
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
