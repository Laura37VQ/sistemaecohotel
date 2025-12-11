<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        /* -------------------------------------------------------
           Filtros recibidos desde Vue
        ------------------------------------------------------- */
        $q      = trim((string)$request->query('q', ''));
        $tipo   = $request->query('tipo', '');
        $estado = $request->query('estado', '');

        /* -------------------------------------------------------
           Construcción dinámica de filtros
        ------------------------------------------------------- */
        $habitaciones = Habitacion::query()

            // Filtro general (número, tipo, descripción)
            ->when($q !== '', function ($query) use ($q) {
                $query->where('numero_habitacion', 'like', "%$q%")
                      ->orWhere('tipo', 'like', "%$q%")
                      ->orWhere('descripcion', 'like', "%$q%");
            })

            // Filtro por tipo
            ->when($tipo !== '', fn($query) => $query->where('tipo', $tipo))

            // Filtro por estado
            ->when($estado !== '', fn($query) => $query->where('estado', $estado))

            ->orderBy('numero_habitacion')
            ->paginate(10)
            ->withQueryString();

        /* -------------------------------------------------------
           Retorno a Inertia con filtros para mantener datos
        ------------------------------------------------------- */
        return inertia('Admin/Habitaciones/Index', [
            'habitaciones' => $habitaciones,
            'filtros'      => [
                'q'      => $q,
                'tipo'   => $tipo,
                'estado' => $estado,
            ]
        ]);
    }

    public function create()
    {
        return inertia('Admin/Habitaciones/HabitacionForm');
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_habitacion'   => 'required|string|max:10|unique:habitaciones,numero_habitacion',
            'tipo'                => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas'  => 'required|integer|min:1',
            'descripcion'         => 'required|string',
            'precio'              => 'required|numeric|min:0',
            'estado'              => 'required|in:Disponible,Ocupada,Mantenimiento,Inactivo',
            'foto'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
        return inertia('Admin/Habitaciones/HabitacionForm', [
            'habitacion' => Habitacion::findOrFail($id)
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
            'tipo'               => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas' => 'required|integer|min:1',
            'descripcion'        => 'required|string',
            'precio'             => 'required|numeric|min:0',
            'estado'             => 'required|in:Disponible,Ocupada,Mantenimiento,Inactivo',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
        Habitacion::findOrFail($id)->delete();

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación eliminada correctamente.');
    }
}
