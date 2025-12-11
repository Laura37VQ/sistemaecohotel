<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        /* -------------------------------------------------------
           Filtros recibidos desde Vue
        ------------------------------------------------------- */
        $q      = trim((string)$request->query('q', ''));
        $rol    = $request->query('rol', '');
        $estado = $request->query('estado', ''); // activo / inactivo

        /* -------------------------------------------------------
           Construcción dinámica de filtros
        ------------------------------------------------------- */
        $usuarios = User::withTrashed()
            ->with('rol')

            //  Filtro general (nombre, correo, documento, usuario)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombres', 'like', "%$q%")
                        ->orWhere('apellidos', 'like', "%$q%")
                        ->orWhere('correo', 'like', "%$q%")
                        ->orWhere('documento_identidad', 'like', "%$q%")
                        ->orWhere('nombre_usuario', 'like', "%$q%");
                });
            })

            //  Filtro por rol
            ->when($rol !== '', fn($query) => $query->where('rol_id', $rol))

            //  Filtro por estado
            ->when($estado !== '', function ($query) use ($estado) {
                if ($estado === "activo") {
                    $query->whereNull('deleted_at');
                }
                if ($estado === "inactivo") {
                    $query->whereNotNull('deleted_at');
                }
            })

            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles'    => Rol::all(),
            'filtros'  => [
                'q'      => $q,
                'rol'    => $rol,
                'estado' => $estado
            ]
        ]);
    }

    public function create()
    {
        return inertia('Admin/Usuarios/UsuarioForm', [
            'roles' => Rol::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rol_id'              => 'required|exists:roles,id',
            'nombres'             => 'required|string|max:150',
            'apellidos'           => 'required|string|max:150',
            'documento_identidad' => 'required|string|max:50|unique:usuarios',
            'fecha_nacimiento'    => 'required|date',
            'correo'              => 'required|email|unique:usuarios',
            'telefono'            => 'required|string|max:20',
            'direccion'           => 'required|string|max:150',
            'nombre_usuario'      => 'required|string|max:50|unique:usuarios',
            'contrasena'          => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'rol_id'              => $request->rol_id,
            'nombres'             => $request->nombres,
            'apellidos'           => $request->apellidos,
            'documento_identidad' => $request->documento_identidad,
            'fecha_nacimiento'    => $request->fecha_nacimiento,
            'correo'              => $request->correo,
            'telefono'            => $request->telefono,
            'direccion'           => $request->direccion,
            'nombre_usuario'      => $request->nombre_usuario,
            'contrasena'          => Hash::make($request->contrasena),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        return inertia('Admin/Usuarios/UsuarioForm', [
            'usuario' => User::withTrashed()->findOrFail($id),
            'roles'   => Rol::all()
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'rol_id'              => 'required|exists:roles,id',
            'nombres'             => 'required|string|max:150',
            'apellidos'           => 'required|string|max:150',
            'documento_identidad' => 'required|string|max:50|unique:usuarios,documento_identidad,' . $usuario->id,
            'fecha_nacimiento'    => 'required|date',
            'correo'              => 'required|email|unique:usuarios,correo,' . $usuario->id,
            'telefono'            => 'required|string|max:20',
            'direccion'           => 'required|string|max:150',
            'nombre_usuario'      => 'required|string|max:50|unique:usuarios,nombre_usuario,' . $usuario->id,
            'contrasena'          => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only([
            'rol_id', 'nombres', 'apellidos', 'documento_identidad',
            'fecha_nacimiento', 'correo', 'telefono', 'direccion', 'nombre_usuario'
        ]);

        if ($request->filled('contrasena')) {
            $data['contrasena'] = Hash::make($request->contrasena);
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::withTrashed()->findOrFail($id);

        if ($usuario->trashed()) {
            $usuario->restore();
            $msg = 'Usuario reactivado correctamente.';
        } else {
            $usuario->delete();
            $msg = 'Usuario desactivado correctamente.';
        }

        return redirect()->route('admin.usuarios.index')->with('success', $msg);
    }
}
