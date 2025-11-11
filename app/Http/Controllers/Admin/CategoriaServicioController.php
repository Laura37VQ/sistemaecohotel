<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaServicioController extends Controller
{
    // Mostrar listado de categorías
    public function index()
    {
        $categorias = CategoriaServicio::orderBy('nombre_categoria')->paginate(10);

        return Inertia::render('Admin/CategoriasServicios/Index', [
            'categorias' => $categorias
        ]);
    }

    // Formulario para crear
    public function create()
    {
        return Inertia::render('Admin/CategoriasServicios/Form', [
            'categoria' => null
        ]);
    }

    // Guardar nueva categoría
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

    // Formulario para editar ( corregido parámetro)
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

    // Actualizar categoría ( corregido parámetro)
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

    // Cambiar estado sin borrar
    public function toggleEstado(CategoriaServicio $categorias_servicio)
    {
        $nuevoEstado = $categorias_servicio->estado === 'Activo' ? 'Inactivo' : 'Activo';
        $categorias_servicio->update(['estado' => $nuevoEstado]);

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Estado actualizado correctamente.');
    }

    // Desactivar categoría
    public function destroy(CategoriaServicio $categorias_servicio)
    {
        $categorias_servicio->update(['estado' => 'Inactivo']);

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría desactivada correctamente.');
    }
}
