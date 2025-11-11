<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegistroController extends Controller
{
    /**
     * Mostrar la página de registro.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Manejar una solicitud de registro.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validación de los campos
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:50|unique:usuarios,nombre_usuario',
            'correo' => 'required|string|email|max:255|unique:usuarios,correo',
            'contrasena' => ['required', 'confirmed', Rules\Password::defaults()],
            'documento_identidad' => 'required|string|max:20|unique:usuarios,documento_identidad',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
        ]);

        // Crear el usuario con rol Cliente (rol_id = 2)
        $user = User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'nombre_usuario' => $request->nombre_usuario,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'documento_identidad' => $request->documento_identidad,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'rol_id' => 2, // Cliente siempre
        ]);

        // Disparar evento de registro (opcional)
        event(new Registered($user));

        // Redirigir al login después del registro
        return to_route('login')->with('success', 'Registro exitoso. Por favor, inicie sesión.');
    }
}
