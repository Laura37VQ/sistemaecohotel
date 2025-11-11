<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdminController extends Controller
{
    // Dashboard del administrador
    public function index()
    {
        return Inertia::render('Admin/Dashboard');
    }

    // Usuarios
    public function usuarios()
    {
        return Inertia::render('Admin/Usuarios/Index');
    }

    // Roles
    public function roles()
    {
        return Inertia::render('Admin/Roles/Index');
    }

    // Habitaciones
    public function habitaciones()
    {
        return Inertia::render('Admin/Habitaciones/Index');
    }

    // Reservas
    public function reservas()
    {
        return Inertia::render('Admin/Reservas/Index');
    }

    // Servicios
    public function servicios()
    {
        return Inertia::render('Admin/Servicios/Index');
    }

    // Facturación
    public function facturacion()
    {
        return Inertia::render('Admin/Facturas/Index');
    }

    // Reportes
    public function reportes()
    {
        return Inertia::render('Admin/Reportes/Index');
    }
}
