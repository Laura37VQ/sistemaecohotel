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
    public function index()
    {
        $servicios = Servicio::with('categoriaServicio')
            ->orderBy('nombre', 'asc')
            ->paginate(10)
            ->through(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'precio' => $servicio->precio,
                    'estado' => $servicio->estado,
                    'categoria' => $servicio->categoriaServicio?->nombre_categoria ?? 'Sin categoría',
                    'foto_url' => $servicio->foto ? asset('storage/' . $servicio->foto) : null,
                ];
            });

        return Inertia::render('Admin/Servicios/Index', [
            'servicios' => $servicios,
        ]);
    }

    public function create()
    {
        $categorias = CategoriaServicio::orderBy('nombre_categoria')->get();

        return Inertia::render('Admin/Servicios/Form', [
            'servicio' => null, // Indica creación
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categoria_servicios,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Activo,Inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('servicios', 'public');
        }

        Servicio::create($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', ' Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        $categorias = CategoriaServicio::orderBy('nombre_categoria')->get();

        return Inertia::render('Admin/Servicios/Form', [
            'servicio' => [
                'id' => $servicio->id,
                'categoria_id' => $servicio->categoria_id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'precio' => $servicio->precio,
                'estado' => $servicio->estado,
                'foto_url' => $servicio->foto ? asset('storage/' . $servicio->foto) : null,
            ],
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categoria_servicios,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|in:Activo,Inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($servicio->foto) {
                Storage::disk('public')->delete($servicio->foto);
            }
            $validated['foto'] = $request->file('foto')->store('servicios', 'public');
        }

        $servicio->update($validated);

        return redirect()->route('admin.servicios.index')
            ->with('success', ' Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        if ($servicio->foto) {
            Storage::disk('public')->delete($servicio->foto);
        }

        $servicio->delete();

        return redirect()->route('admin.servicios.index')
            ->with('success', ' Servicio eliminado correctamente.');
    }
}
