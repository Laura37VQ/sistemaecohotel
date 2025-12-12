<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reservas';

    protected $fillable = [
        'usuario_id',
        'habitacion_id',
        'codigo_reserva',
        'fecha_ingreso',
        'fecha_salida',
        'estado',
        'descripcion',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id')
                    ->withTrashed();
    }

    // Relación con habitación
    public function habitacion()
    {
        return $this->belongsTo(\App\Models\Habitacion::class, 'habitacion_id')
                    ->withTrashed();
    }

    // Relación con factura
    public function factura()
    {
        return $this->hasOne(\App\Models\Factura::class, 'reserva_id');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'detalle_reservas')
                    ->withPivot('cantidad', 'precio_unitario');
    }
}

