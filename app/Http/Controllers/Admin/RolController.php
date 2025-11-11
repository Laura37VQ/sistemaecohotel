<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withTrashed()->orderBy('id')->paginate(10);
        return inertia('Admin/Roles/Index', compact('roles'));
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/RolForm');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_rol' => 'required|string|max:50|unique:roles',
        ]);

        Rol::create([
            'nombre_rol' => $request->nombre_rol,
        ]);

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Rol creado correctamente.');
    }

    public function edit($id)
    {
        $rol = Rol::withTrashed()->findOrFail($id);
        return Inertia::render('Admin/Roles/RolForm', [
            'rol' => $rol
        ]);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::withTrashed()->findOrFail($id);

        $request->validate([
            'nombre_rol' => 'required|string|max:50|unique:roles,nombre_rol,' . $rol->id,
        ]);

        $rol->nombre_rol = $request->nombre_rol;
        $rol->save();

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy($id)
    {
        $rol = Rol::withTrashed()->findOrFail($id);

        if ($rol->trashed()) {
            $rol->restore();
            $mensaje = 'Rol reactivado correctamente.';
        } else {
            $rol->delete();
            $mensaje = 'Rol desactivado correctamente.';
        }

        return redirect()->route('admin.roles.index')
                         ->with('success', $mensaje);
    }
}
