<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/*
  Este controlador administra todas las acciones relacionadas con las habitaciones:

  - Listar habitaciones con filtros
  - Crear nuevas habitaciones
  - Editar habitaciones
  - Desactivar habitaciones (soft delete)
  - Restaurar habitaciones
*/
class HabitacionController extends Controller
{
    // Mostrar listado de habitaciones (incluye activas y desactivadas)
    public function index(Request $request)
    {
        // Filtros recibidos desde la vista
        $q      = trim((string)$request->query('q', ''));
        $tipo   = $request->query('tipo', '');
        $estado = $request->query('estado', '');

        // Consulta con filtros. withTrashed() permite incluir desactivadas
        $habitaciones = Habitacion::withTrashed()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('numero_habitacion', 'like', "%$q%")
                       ->orWhere('tipo', 'like', "%$q%")
                       ->orWhere('descripcion', 'like', "%$q%");
                });
            })
            ->when($tipo !== '', fn($q2) => $q2->where('tipo', $tipo))
            ->when($estado !== '', fn($q2) => $q2->where('estado', $estado))
            ->orderBy('numero_habitacion')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Habitaciones/Index', [
            'habitaciones' => $habitaciones,
            'filtros' => [
                'q'      => $q,
                'tipo'   => $tipo,
                'estado' => $estado,
            ]
        ]);
    }

    // Mostrar formulario para crear habitación
    public function create()
    {
        return inertia('Admin/Habitaciones/HabitacionForm');
    }

    // Guardar una habitación nueva
    public function store(Request $request)
    {
        /*
          Validamos campos obligatorios.
          La foto es obligatoria al crear una habitación
          y se le muestra al usuario un mensaje claro.
        */
        $request->validate([
            'numero_habitacion'   => 'required|string|max:10|unique:habitaciones,numero_habitacion',
            'tipo'                => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas'  => 'required|integer|min:1',
            'descripcion'         => 'required|string',
            'precio'              => 'required|numeric|min:0',
            'estado'              => 'required|in:Disponible,Ocupada,Mantenimiento',
            'foto'                => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'foto.required' => 'Debes subir una imagen de la habitación.',
            'foto.image'    => 'El archivo debe ser una imagen.',
            'foto.mimes'    => 'Solo se permiten JPG o PNG.',
            'foto.max'      => 'La imagen debe ser máximo de 2MB.',
        ]);

        // Se toman los datos normales
        $data = $request->only([
            'numero_habitacion',
            'tipo',
            'capacidad_personas',
            'descripcion',
            'precio',
            'estado',
        ]);

        // Guardamos la imagen en /storage/app/public/habitaciones
        $data['foto'] = $request->file('foto')->store('habitaciones', 'public');

        Habitacion::create($data);

        return back()->with('success', 'Habitación creada correctamente.');
    }

    // Mostrar el formulario de edición
    public function edit($id)
    {
        return inertia('Admin/Habitaciones/HabitacionForm', [
            'habitacion' => Habitacion::withTrashed()->findOrFail($id)
        ]);
    }

    // Actualizar habitación existente
    public function update(Request $request, $id)
    {
        $habitacion = Habitacion::withTrashed()->findOrFail($id);

        /*
          Validamos los campos. La foto aquí es opcional.
        */
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
            'estado'             => 'required|in:Disponible,Ocupada,Mantenimiento',
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('foto');

        // Si se subió foto nueva, se guarda
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('habitaciones', 'public');
        }

        $habitacion->update($data);

        return back()->with('success', 'Habitación actualizada correctamente.');
    }

    // DESACTIVAR habitación (soft delete)
    public function destroy($id)
    {
        $habitacion = Habitacion::findOrFail($id);

        /*
          delete() NO elimina la habitación,
          solo llena la columna "deleted_at".
          Esto permite restaurarla más adelante.
        */
        $habitacion->delete();

        return back()->with('success', 'La habitación fue desactivada.');
    }

    // Restaurar una habitación desactivada
    public function restore($id)
    {
        $habitacion = Habitacion::withTrashed()->findOrFail($id);

        $habitacion->restore(); // Quita el deleted_at

        return back()->with('success', 'La habitación fue reactivada.');
    }
}
