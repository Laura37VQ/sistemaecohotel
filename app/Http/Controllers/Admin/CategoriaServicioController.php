<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaServicioController extends Controller
{
    /* 
       LISTADO CON FILTROS
     */
    public function index(Request $request)
    {
        // Recibir filtros desde Vue
        $q      = trim((string)$request->query('q', ''));
        $estado = $request->query('estado', '');

        /*
           Construcción dinámica de la consulta
         */
        $categorias = CategoriaServicio::query()

            // Buscar por nombre o descripción
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nombre_categoria', 'like', "%$q%")
                      ->orWhere('descripcion', 'like', "%$q%");
            })

            // Filtrar por estado
            ->when($estado !== '', function ($query) use ($estado) {
                $query->where('estado', $estado);
            })

            ->orderBy('nombre_categoria')
            ->paginate(10)
            ->withQueryString(); // Mantiene filtros en paginación

        return Inertia::render('Admin/CategoriasServicios/Index', [
            'categorias' => $categorias,
            'filtros' => [
                'q'      => $q,
                'estado' => $estado,
            ]
        ]);
    }

    // ---------------------------------------------
    // RESTO DEL CRUD (no cambia)
    // ---------------------------------------------

    public function create()
    {
        return Inertia::render('Admin/CategoriasServicios/Form', [
            'categoria' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_categoria' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo'
        ]);

        CategoriaServicio::create($request->all());

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaServicio $categorias_servicio)
    {
        return Inertia::render('Admin/CategoriasServicios/Form', [
            'categoria' => [
                'id' => $categorias_servicio->id,
                'nombre_categoria' => $categorias_servicio->nombre_categoria,
                'descripcion' => $categorias_servicio->descripcion,
                'estado' => $categorias_servicio->estado,
            ]
        ]);
    }

    public function update(Request $request, CategoriaServicio $categorias_servicio)
    {
        $request->validate([
            'nombre_categoria' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo'
        ]);

        $categorias_servicio->update($request->all());

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function toggleEstado(CategoriaServicio $categorias_servicio)
    {
        $nuevoEstado = $categorias_servicio->estado === 'Activo' ? 'Inactivo' : 'Activo';

        $categorias_servicio->update(['estado' => $nuevoEstado]);

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function destroy(CategoriaServicio $categorias_servicio)
    {
        $categorias_servicio->update(['estado' => 'Inactivo']);

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría desactivada correctamente.');
    }
}
