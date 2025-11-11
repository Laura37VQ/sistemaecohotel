<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        $clientes = User::withTrashed() // para ver activos e inactivos (opcional)
            ->where('rol_id', 2)
            ->when($q, function ($qry) use ($q) {
                $qLike = "%{$q}%";
                $qry->where(function ($sub) use ($qLike) {
                    $sub->where('nombres', 'like', $qLike)
                        ->orWhere('apellidos', 'like', $qLike)
                        ->orWhere('documento_identidad', 'like', $qLike)
                        ->orWhere('correo', 'like', $qLike)
                        ->orWhere('telefono', 'like', $qLike)
                        ->orWhere('nombre_usuario', 'like', $qLike);
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        // Normalización mínima para la vista
        $clientes->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'nombres' => $u->nombres,
                'apellidos' => $u->apellidos,
                'documento_identidad' => $u->documento_identidad,
                'correo' => $u->correo,
                'telefono' => $u->telefono,
                'direccion' => $u->direccion,
                'nombre_usuario' => $u->nombre_usuario,
                'deleted_at' => $u->deleted_at,
            ];
        });

        return Inertia::render('Recepcionista/Clientes/Index', [
            'clientes' => $clientes,
            'filtros' => [
                'q' => $q,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Recepcionista/Clientes/Form', [
            'cliente' => null,
        ]);
    }

    public function store(Request $request)
    {
        // Reglas con unique ignorando soft-deleted
        $rules = [
            'nombres' => ['required','string','max:150'],
            'apellidos' => ['required','string','max:150'],
            'documento_identidad' => [
                'required','string','max:50',
                Rule::unique('usuarios','documento_identidad')->whereNull('deleted_at')
            ],
            'fecha_nacimiento' => ['required','date'],
            'correo' => [
                'required','email','max:150',
                Rule::unique('usuarios','correo')->whereNull('deleted_at')
            ],
            'telefono' => ['required','string','max:20'],
            'direccion' => ['required','string','max:150'],
            'nombre_usuario' => [
                'required','string','max:50',
                Rule::unique('usuarios','nombre_usuario')->whereNull('deleted_at')
            ],
            'contrasena' => ['nullable','string','min:6'], // si no envías, generamos una
        ];

        $data = $request->validate($rules);

        $password = $data['contrasena'] ?? substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);

        User::create([
            'rol_id' => 2, // Cliente
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'documento_identidad' => $data['documento_identidad'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'correo' => $data['correo'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'],
            'nombre_usuario' => $data['nombre_usuario'],
            'contrasena' => Hash::make($password),
        ]);

        return redirect()->route('recepcionista.clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(User $cliente)
    {
        // Garantiza que sea cliente
        abort_if((int)$cliente->rol_id !== 2, 403);

        return Inertia::render('Recepcionista/Clientes/Form', [
            'cliente' => [
                'id' => $cliente->id,
                'nombres' => $cliente->nombres,
                'apellidos' => $cliente->apellidos,
                'documento_identidad' => $cliente->documento_identidad,
                'fecha_nacimiento' => optional($cliente->fecha_nacimiento)->format('Y-m-d'),
                'correo' => $cliente->correo,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'nombre_usuario' => $cliente->nombre_usuario,
            ],
        ]);
    }

    public function update(Request $request, User $cliente)
    {
        abort_if((int)$cliente->rol_id !== 2, 403);

        $rules = [
            'nombres' => ['required','string','max:150'],
            'apellidos' => ['required','string','max:150'],
            'documento_identidad' => [
                'required','string','max:50',
                Rule::unique('usuarios','documento_identidad')->ignore($cliente->id, 'id')->whereNull('deleted_at')
            ],
            'fecha_nacimiento' => ['required','date'],
            'correo' => [
                'required','email','max:150',
                Rule::unique('usuarios','correo')->ignore($cliente->id, 'id')->whereNull('deleted_at')
            ],
            'telefono' => ['required','string','max:20'],
            'direccion' => ['required','string','max:150'],
            'nombre_usuario' => [
                'required','string','max:50',
                Rule::unique('usuarios','nombre_usuario')->ignore($cliente->id, 'id')->whereNull('deleted_at')
            ],
            'contrasena' => ['nullable','string','min:6'], // si la envían, la cambiamos
        ];

        $data = $request->validate($rules);

        $payload = [
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'documento_identidad' => $data['documento_identidad'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'correo' => $data['correo'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'],
            'nombre_usuario' => $data['nombre_usuario'],
        ];

        if (!empty($data['contrasena'])) {
            $payload['contrasena'] = Hash::make($data['contrasena']);
        }

        $cliente->update($payload);

        return redirect()->route('recepcionista.clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    // Desactivar (soft-delete)
    public function destroy(User $cliente)
    {
        abort_if((int)$cliente->rol_id !== 2, 403);

        $cliente->delete();

        return redirect()->route('recepcionista.clientes.index')
            ->with('success', 'Cliente desactivado correctamente.');
    }

    // Reactivar (restore)
    public function restore($id)
    {
        $cliente = User::withTrashed()->findOrFail($id);
        abort_if((int)$cliente->rol_id !== 2, 403);

        $cliente->restore();

        return redirect()->route('recepcionista.clientes.index')
            ->with('success', 'Cliente reactivado correctamente.');
    }
}
