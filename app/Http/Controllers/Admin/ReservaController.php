<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $reservas = Reserva::with(['usuario', 'habitacion', 'factura'])
            ->orderByDesc('created_at')
            ->paginate(10);

        // Si es recepcionista, renderiza vista de recepcionista
        if ($user->rol && $user->rol->nombre_rol === 'Recepcionista') {
            return Inertia::render('Recepcionista/Reservas/Index', compact('reservas'));
        }

        // Si es admin (u otro), renderiza vista admin
        return Inertia::render('Admin/Reservas/Index', compact('reservas'));
    }

    public function create()
    {
        $user = Auth::user();

        $habitaciones = Habitacion::where('estado', 'Disponible')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'numero_habitacion' => $h->numero_habitacion,
                'tipo' => $h->tipo
            ]);

        // Si es cliente, solo puede reservar para sí mismo
        if ((int)$user->rol_id === 2) {
            $usuarios = collect([$user])->map(fn($u) => [
                'id' => $u->id,
                'nombres' => $u->nombres,
                'apellidos' => $u->apellidos
            ]);
        } else {
            $usuarios = User::where('rol_id', 2)
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'nombres' => $u->nombres,
                    'apellidos' => $u->apellidos
                ]);
        }

        $vista = ($user->rol && $user->rol->nombre_rol === 'Recepcionista')
            ? 'Recepcionista/Reservas/ReservaForm'
            : 'Admin/Reservas/ReservaForm';

        return Inertia::render($vista, [
            'usuarios' => $usuarios,
            'habitaciones' => $habitaciones,
            'reserva' => null,
            'rol' => (int)$user->rol_id
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'usuario_id' => 'required|exists:usuarios,id',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_ingreso',
            'estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
            'descripcion' => 'nullable|string',
        ];

        if ((int)$user->rol_id === 2) {
            $rules['fecha_ingreso'] .= '|after_or_equal:today';
        }

        $request->validate($rules);

        $codigo = 'RES-' . strtoupper(Str::random(6));

        Reserva::create([
            'usuario_id' => $request->usuario_id,
            'habitacion_id' => $request->habitacion_id,
            'codigo_reserva' => $codigo,
            'fecha_ingreso' => $request->fecha_ingreso,
            'fecha_salida' => $request->fecha_salida,
            'estado' => $request->estado,
            'descripcion' => $request->descripcion,
        ]);

        $ruta = ($user->rol && $user->rol->nombre_rol === 'Recepcionista')
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva creada correctamente.');
    }

    public function edit($id)
    {
        $reserva = Reserva::findOrFail($id);
        $user = Auth::user();

        $usuarios = User::where('rol_id', 2)->get()->map(fn($u) => [
            'id' => $u->id,
            'nombres' => $u->nombres,
            'apellidos' => $u->apellidos
        ]);

        $habitaciones = Habitacion::all()->map(fn($h) => [
            'id' => $h->id,
            'numero_habitacion' => $h->numero_habitacion,
            'tipo' => $h->tipo
        ]);

        $vista = ($user->rol && $user->rol->nombre_rol === 'Recepcionista')
            ? 'Recepcionista/Reservas/ReservaForm'
            : 'Admin/Reservas/ReservaForm';

        return Inertia::render($vista, compact('reserva', 'usuarios', 'habitaciones'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        $user = Auth::user();

        $rules = [
            'usuario_id' => 'required|exists:usuarios,id',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_ingreso',
            'estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
            'descripcion' => 'nullable|string',
        ];

        if ((int)$user->rol_id === 2) {
            $rules['fecha_ingreso'] .= '|after_or_equal:today';
        }

        $request->validate($rules);

        $reserva->update($request->only([
            'usuario_id',
            'habitacion_id',
            'fecha_ingreso',
            'fecha_salida',
            'estado',
            'descripcion'
        ]));

        $ruta = ($user->rol && $user->rol->nombre_rol === 'Recepcionista')
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva actualizada correctamente.');
    }

    public function actualizarEstado(Request $request, \App\Models\Reserva $reserva)
    {
        // Validar nuevo estado
        $data = $request->validate([
            'estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada',
        ]);
        
        $estadoNuevo = $data['estado'];
        $estadoActual = $reserva->estado;
        
        $transicionValida = false;
        if ($estadoActual === 'Pendiente' && $estadoNuevo === 'Confirmada') $transicionValida = true;
        if ($estadoActual === 'Confirmada' && $estadoNuevo === 'Completada') $transicionValida = true;
        if ($estadoNuevo === 'Cancelada') $transicionValida = true;
        if ($estadoActual === 'Pendiente' && $estadoNuevo === 'Pendiente') $transicionValida = true; // sin cambio
        
        if (!$transicionValida) {
            return back()->withErrors(['estado' => "Transición inválida de $estadoActual a $estadoNuevo"]);
        }

        // Guardar estado
        $reserva->estado = $estadoNuevo;
        $reserva->save();
        
        // Actualizar estado de la habitación
        // - Confirmada => Ocupada
        // - Completada o Cancelada => Disponible
        if ($estadoNuevo === 'Confirmada') {
            $reserva->habitacion()->update(['estado' => 'Ocupada']);
        
        } elseif (in_array($estadoNuevo, ['Completada', 'Cancelada'])) {
            $reserva->habitacion()->update(['estado' => 'Disponible']);
        }
        
        return back()->with('success', "Estado actualizado a: {$estadoNuevo}");
    }


    public function destroy(Reserva $reserva)
    {
        $user = Auth::user();

        $reserva->delete();

        $ruta = ($user->rol && $user->rol->nombre_rol === 'Recepcionista')
            ? 'recepcionista.reservas.index'
            : 'admin.reservas.index';

        return redirect()->route($ruta)->with('success', 'Reserva eliminada correctamente.');
    }
}
