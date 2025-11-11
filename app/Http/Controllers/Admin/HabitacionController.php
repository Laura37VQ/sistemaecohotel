<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::orderBy('numero_habitacion')->paginate(10);
        return inertia('Admin/Habitaciones/Index', compact('habitaciones'));
    }

    public function create()
    {
        return inertia('Admin/Habitaciones/HabitacionForm');
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_habitacion' => 'required|string|max:10|unique:habitaciones,numero_habitacion',
            'tipo' => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas' => 'required|integer|min:1',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Disponible,Ocupada,Mantenimiento,Inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'numero_habitacion',
            'tipo',
            'capacidad_personas',
            'descripcion',
            'precio',
            'estado'
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('habitaciones', 'public');
        }

        Habitacion::create($data);

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación creada correctamente.');
    }

    public function edit($id)
    {
        $habitacion = Habitacion::findOrFail($id);

        return inertia('Admin/Habitaciones/HabitacionForm', [
            'habitacion' => $habitacion
        ]);
    }

    public function update(Request $request, $id)
    {
        $habitacion = Habitacion::findOrFail($id);

        $request->validate([
            'numero_habitacion' => [
                'required',
                'string',
                'max:10',
                Rule::unique('habitaciones', 'numero_habitacion')->ignore($habitacion->id),
            ],
            'tipo' => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas' => 'required|integer|min:1',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Disponible,Ocupada,Mantenimiento,Inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('habitaciones', 'public');
        }

        $habitacion->update($data);

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación actualizada correctamente.');
    }

    public function destroy($id)
    {
        $habitacion = Habitacion::findOrFail($id);
        $habitacion->delete();

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación eliminada correctamente.');
    }
}
