<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\CategoriaServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServicioController extends Controller
{
    /* ================================================================
       LISTADO CON FILTROS
    ================================================================= */
    public function index(Request $request)
    {
        $q          = trim($request->query('q', ''));
        $categoria  = $request->query('categoria', '');
        $estado     = $request->query('estado', '');
        $precioMin  = $request->query('precio_min', '');
        $precioMax  = $request->query('precio_max', '');

        $servicios = Servicio::with('categoriaServicio')

            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%$q%")
                        ->orWhere('descripcion', 'like', "%$q%");
                });
            })

            ->when($categoria !== '', fn($query) => $query->where('categoria_id', $categoria))

            ->when($estado !== '', fn($query) => $query->where('estado', $estado))

            ->when($precioMin !== '', fn($query) => $query->where('precio', '>=', $precioMin))

            ->when($precioMax !== '', fn($query) => $query->where('precio', '<=', $precioMax))

            ->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString()

            ->through(function ($s) {
                return [
                    'id'        => $s->id,
                    'nombre'    => $s->nombre,
                    'descripcion' => $s->descripcion,
                    'precio'    => $s->precio,
                    'estado'    => $s->estado,
                    'categoria' => $s->categoriaServicio?->nombre_categoria ?? 'Sin categoría',
                    'foto_url'  => $s->foto ? asset('storage/' . $s->foto) : null,
                ];
            });

        return Inertia::render('Admin/Servicios/Index', [
            'servicios'  => $servicios,
            'categorias' => CategoriaServicio::where('estado', 'Activo')
                                ->orderBy('nombre_categoria')
                                ->get(),
            'filtros' => [
                'q'          => $q,
                'categoria'  => $categoria,
                'estado'     => $estado,
                'precio_min' => $precioMin,
                'precio_max' => $precioMax,
            ]
        ]);
    }

    /* ================================================================
       FORM – CREAR
    ================================================================= */
    public function create()
    {
        return Inertia::render('Admin/Servicios/Form', [
            'servicio'   => null,
            'categorias' => CategoriaServicio::where('estado', 'Activo')
                                ->orderBy('nombre_categoria')
                                ->get()
        ]);
    }

    /* ================================================================
       GUARDAR NUEVO
    ================================================================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categoria_servicios,id',
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'estado'       => 'required|in:Activo,Inactivo',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('servicios', 'public');
        }

        Servicio::create($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    /* ================================================================
       EDITAR
    ================================================================= */
    public function edit(Servicio $servicio)
    {
        return Inertia::render('Admin/Servicios/Form', [
            'servicio' => [
                'id'           => $servicio->id,
                'categoria_id' => $servicio->categoria_id,
                'nombre'       => $servicio->nombre,
                'descripcion'  => $servicio->descripcion,
                'precio'       => $servicio->precio,
                'estado'       => $servicio->estado,
                'foto_url'     => $servicio->foto ? asset('storage/' . $servicio->foto) : null,
            ],
            'categorias' => CategoriaServicio::where('estado', 'Activo')
                                ->orderBy('nombre_categoria')
                                ->get()
        ]);
    }

    /* ================================================================
       ACTUALIZAR
    ================================================================= */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categoria_servicios,id',
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'estado'       => 'required|in:Activo,Inactivo',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($servicio->foto) {
                Storage::disk('public')->delete($servicio->foto);
            }
            $validated['foto'] = $request->file('foto')->store('servicios', 'public');
        }

        $servicio->update($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    /* ================================================================
       ACTIVAR / DESACTIVAR (NO BORRA)
    ================================================================= */
    public function toggleEstado(Servicio $servicio)
    {
        $nuevo = $servicio->estado === 'Activo' ? 'Inactivo' : 'Activo';

        $servicio->update(['estado' => $nuevo]);

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

    /* ================================================================
       ELIMINAR
    ================================================================= */
    public function destroy(Servicio $servicio)
    {
        if ($servicio->foto) {
            Storage::disk('public')->delete($servicio->foto);
        }

        $servicio->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }
}
