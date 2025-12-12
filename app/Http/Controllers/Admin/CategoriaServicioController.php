<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaServicioController extends Controller
{
    /* =====================================================
       LISTADO CON FILTROS
    ====================================================== */
    public function index(Request $request)
    {
        $q      = trim((string)$request->query('q', ''));
        $estado = $request->query('estado', '');

        $categorias = CategoriaServicio::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nombre_categoria', 'like', "%$q%")
                      ->orWhere('descripcion', 'like', "%$q%");
            })
            ->when($estado !== '', function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->orderBy('nombre_categoria')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/CategoriasServicios/Index', [
            'categorias' => $categorias,
            'filtros' => [
                'q'      => $q,
                'estado' => $estado,
            ]
        ]);
    }

    /* =====================================================
       CREAR
    ====================================================== */
    public function create()
    {
        return Inertia::render('Admin/CategoriasServicios/Form', [
            'categoria' => null
        ]);
    }

    /* =====================================================
       GUARDAR
    ====================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_categoria' => 'required|string|max:100',
            'descripcion'      => 'nullable|string',
            'estado'           => 'required|in:Activo,Inactivo'
        ]);

        CategoriaServicio::create($request->all());

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    /* =====================================================
       EDITAR
    ====================================================== */
    public function edit(CategoriaServicio $categorias_servicio)
    {
        return Inertia::render('Admin/CategoriasServicios/Form', [
            'categoria' => [
                'id'                => $categorias_servicio->id,
                'nombre_categoria'  => $categorias_servicio->nombre_categoria,
                'descripcion'       => $categorias_servicio->descripcion,
                'estado'            => $categorias_servicio->estado,
            ]
        ]);
    }

    /* =====================================================
       ACTUALIZAR
    ====================================================== */
    public function update(Request $request, CategoriaServicio $categorias_servicio)
    {
        $request->validate([
            'nombre_categoria' => 'required|string|max:100',
            'descripcion'      => 'nullable|string',
            'estado'           => 'required|in:Activo,Inactivo'
        ]);

        $categorias_servicio->update($request->all());

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    /* =====================================================
       ACTIVAR / INACTIVAR (NO BORRA)
    ====================================================== */
    public function toggleEstado(CategoriaServicio $categorias_servicio)
    {
        $nuevoEstado = $categorias_servicio->estado === 'Activo'
            ? 'Inactivo'
            : 'Activo';

        $categorias_servicio->update(['estado' => $nuevoEstado]);

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Estado actualizado correctamente.');
    }

    /* =====================================================
       ELIMINAR (SOFTDELETE REAL)
       NO PERMITE BORRAR SI TIENE SERVICIOS ASOCIADOS
    ====================================================== */
    public function destroy(CategoriaServicio $categorias_servicio)
    {
        // Verificar si tiene servicios asignados
        if ($categorias_servicio->servicios()->count() > 0) {
            return redirect()->route('admin.categorias-servicios.index')
                ->with('error', 'No se puede eliminar: la categoría tiene servicios asociados.');
        }

        // Cambiar estado a Inactivo antes de borrar
        $categorias_servicio->update(['estado' => 'Inactivo']);

        // Soft delete
        $categorias_servicio->delete();

        return redirect()->route('admin.categorias-servicios.index')
            ->with('success', 'Categoría eliminada (archivada) correctamente.');
    }
}
