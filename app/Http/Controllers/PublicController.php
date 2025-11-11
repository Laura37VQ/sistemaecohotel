<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\CategoriaServicio;
use App\Models\InformacionHotel;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function home()
    {
        $habitaciones = Habitacion::where('estado', 'Disponible')
            ->take(6)
            ->get(['id', 'numero_habitacion', 'tipo', 'descripcion', 'precio', 'foto']);

        $categorias = CategoriaServicio::with(['servicios' => function ($q) {
            $q->where('estado', 'Activo');
        }])->where('estado', 'Activo')->get();

        $info = InformacionHotel::first();

        return Inertia::render('Public/Home', [
            'habitaciones' => $habitaciones,
            'categorias' => $categorias,
            'info' => $info,
        ]);
    }

    public function login()
    {
        $info = InformacionHotel::first();
        return Inertia::render('Auth/Login', [
            'info' => $info,
        ]);
    }

    public function register()
    {
        $info = InformacionHotel::first();
        return Inertia::render('Auth/Register', [
            'info' => $info,
        ]);
    }
}
