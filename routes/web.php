<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\HabitacionController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\CategoriaServicioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\ReservaController;
use App\Http\Controllers\Admin\FacturacionController;
use App\Http\Controllers\Admin\DetalleFacturaController;
use App\Http\Controllers\Admin\InformacionHotelController;
use App\Http\Controllers\Admin\ReservaController as AdminReservaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Recepcionista\RecepcionistaController;
use App\Http\Controllers\Recepcionista\FacturaController;
use App\Http\Controllers\Recepcionista\ClienteController as RecepcionistaClienteController;
use App\Http\Controllers\PublicController;

//  Página de inicio pública
Route::get('/', [PublicController::class, 'home'])->name('inicio');


//  Redirección general al dashboard según el rol
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->rol_id == 1) return redirect()->route('dashboard.admin');
    if ($user->rol_id == 2) return redirect()->route('dashboard.cliente');
    if ($user->rol_id == 3) return redirect()->route('dashboard.recepcionista');

    abort(403);
})->middleware(['auth'])->name('dashboard');

//  Rutas protegidas (requieren inicio de sesión)
Route::middleware(['auth'])->group(function () {

    // --- DASHBOARDS ---
    Route::get('/dashboard/admin', [AdminController::class, 'index'])->name('dashboard.admin');
    Route::get('/dashboard/cliente', [ClienteController::class, 'index'])->name('dashboard.cliente');
    Route::get('/dashboard/recepcionista', [RecepcionistaController::class, 'index'])->name('dashboard.recepcionista');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // --- ADMIN ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);


        // CRUD habitaciones
        Route::resource('habitaciones', HabitacionController::class)->except(['show']);

        Route::put('habitaciones/{id}/restore', [HabitacionController::class, 'restore'])
            ->name('habitaciones.restore');

        Route::resource('servicios', ServicioController::class);

        Route::patch('servicios/{servicio}/toggle', 
            [ServicioController::class, 'toggleEstado']
        )->name('servicios.toggle');

        Route::resource('categorias-servicios', CategoriaServicioController::class);

        // Activar / inactivar categoría
        Route::patch('categorias-servicios/{categorias_servicio}/toggle',
            [CategoriaServicioController::class, 'toggleEstado']
        )->name('categorias-servicios.toggle'); 

        // Eliminar (SoftDelete)
        Route::delete('categorias-servicios/{categoria}', 
            [CategoriaServicioController::class, 'destroy']
        )->name('categorias-servicios.destroy');


        Route::resource('roles', RolController::class)->except(['show']);
        Route::resource('reservas', ReservaController::class)->except(['show']);

        Route::patch('reservas/{reserva}/estado', 
            [ReservaController::class, 'actualizarEstado']
        )->name('reservas.estado');
        

        // Información del hotel
        Route::get('informacion-hotel', [InformacionHotelController::class, 'index'])->name('informacion-hotel.index');
        Route::get('informacion-hotel/edit', [InformacionHotelController::class, 'edit'])->name('informacion-hotel.edit');
        Route::post('informacion-hotel/save', [InformacionHotelController::class, 'save'])->name('informacion-hotel.save');

        //  Facturas del administrador
        Route::prefix('facturas')->name('facturas.')->group(function () {
            Route::get('/', [FacturacionController::class, 'index'])->name('index');
            Route::get('/create', [FacturacionController::class, 'create'])->name('create');
            Route::post('/', [FacturacionController::class, 'store'])->name('store');
            Route::get('/{factura}', [FacturacionController::class, 'show'])->name('show');
            Route::get('/{factura}/edit', [FacturacionController::class, 'edit'])->name('edit');
            Route::put('/{factura}', [FacturacionController::class, 'update'])->name('update');
            
            //  Anular factura (en lugar de eliminar)
            Route::put('/{factura}/anular', [FacturacionController::class, 'anular'])->name('anular');
            
            //  Descargar PDF
            Route::get('/{factura}/pdf', [FacturacionController::class, 'descargarPDF'])->name('pdf');
            
            //  Agregar / eliminar detalles (servicios)
            Route::post('/{factura}/detalles', [FacturacionController::class, 'agregarDetalle'])->name('detalles.store');
            Route::delete('/detalles/{detalle}', [FacturacionController::class, 'eliminarDetalle'])->name('detalles.destroy');
        
        });

        // Reportes normales
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/ocupacion', [ReporteController::class, 'ocupacion'])->name('reportes.ocupacion');
        Route::get('/reportes/ingresos', [ReporteController::class, 'ingresos'])->name('reportes.ingresos');
        Route::get('/reportes/clientes', [ReporteController::class, 'clientes'])->name('reportes.clientes');
        
        // PDF de ingresos
        Route::get('/reportes/ingresos/pdf', [ReporteController::class, 'pdfIngresos'])->name('reportes.ingresos.pdf');

        // PDF de clientes 
        Route::get('/reportes/clientes/pdf', [ReporteController::class, 'pdfClientes'])->name('reportes.clientes.pdf');

        // PDF de ocupación
        Route::get('/reportes/ocupacion/pdf', [ReporteController::class, 'pdfOcupacion'])->name('reportes.ocupacion.pdf');


    });

    // --- RECEPCIONISTA ---
    Route::prefix('recepcionista')->name('recepcionista.')->group(function () {
        Route::get('/reservas/menu', [RecepcionistaController::class, 'reservas'])->name('reservas.menu');
        Route::get('/facturas', [RecepcionistaController::class, 'facturas'])->name('facturas.menu');
        Route::get('checkin-checkout', [RecepcionistaController::class, 'checkinCheckout'])->name('checkin');

        // Reservas (usa el controlador admin)
        Route::resource('reservas', AdminReservaController::class)->except(['show'])->names('reservas');
        Route::patch('reservas/{reserva}/estado', [AdminReservaController::class, 'actualizarEstado'])->name('reservas.estado');

        // Clientes
        Route::resource('clientes', RecepcionistaClienteController::class)->except(['show'])->names('clientes');
        Route::post('clientes/{cliente}/restore', [RecepcionistaClienteController::class, 'restore'])->name('clientes.restore');

        // Facturas
        Route::resource('facturas', FacturaController::class)->except(['show'])->names('facturas');
        Route::get('facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show');
        
        //  Cálculo de totales por reserva
        Route::get('reservas/{reserva}/calcular', [FacturaController::class, 'calcularTotales'])->name('reservas.calcular');
        
        //  Detalles (servicios adicionales)
        Route::post('facturas/{factura}/detalles', [FacturaController::class, 'agregarDetalle'])->name('facturas.detalles.agregar');
        Route::delete('facturas/detalles/{detalle}',
            [\App\Http\Controllers\Recepcionista\DetalleFacturaController::class, 'destroy']
        )->name('facturas.detalles.eliminar');
        
        //  Acciones de control
        Route::post('facturas/{factura}/pagar', [FacturaController::class, 'marcarPagada'])->name('facturas.pagar');
        Route::get('facturas/{factura}/pdf', [FacturaController::class, 'descargarPDF'])->name('facturas.pdf');
    });

    // --- CLIENTE ---
    Route::prefix('cliente')->name('cliente.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ClienteController::class, 'index'])->name('dashboard');
        
        // Disponibilidad y reservas
        Route::get('/disponibilidad', [ClienteController::class, 'disponibilidad'])->name('disponibilidad');
        Route::post('/buscar-disponibilidad', [ClienteController::class, 'buscarDisponibilidad'])->name('buscarDisponibilidad');
        
        // Proceso de reserva
        Route::get('/reservar/{habitacion}', [ClienteController::class, 'reservar'])->name('reservar');
        Route::post('/reservar', [ClienteController::class, 'storeReserva'])->name('storeReserva');
        
        // Servicios
        Route::get('/servicios/{reserva}', [ClienteController::class, 'servicios'])->name('servicios');
        Route::post('/servicios/{reserva}', [ClienteController::class, 'storeServicios'])->name('storeServicios');
        
        //  Listar reservas del cliente
        Route::get('/reservas', [ClienteController::class, 'misReservas'])->name('reservas');
        
        //  Listar facturas del cliente
        Route::get('/facturacion', [ClienteController::class, 'verFacturas'])->name('facturacion'); //  

        //  Descargar PDF de una factura
        Route::get('/factura/descargar/{factura}', [ClienteController::class, 'descargarFactura'])->name('factura.descargar');
        
        // Editar reserva
        Route::get('/reservas/{reserva}/editar', [ClienteController::class, 'editarReserva'])->name('reservas.editar');
        Route::put('/reservas/{reserva}', [ClienteController::class, 'actualizarReserva'])->name('reservas.actualizar');
        
        // Cancelar reserva
        Route::delete('/reservas/{reserva}', [ClienteController::class, 'cancelarReserva'])->name('reservas.cancelar');

        Route::get('/cliente/servicios', [ClienteController::class, 'servicios'])
            ->name('cliente.servicios');
    });

});

// Archivos externos
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
