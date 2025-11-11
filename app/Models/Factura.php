<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Reserva;
use App\Models\DetalleFactura;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | CONSTANTES DE ESTADO
    |--------------------------------------------------------------------------
    | Se define los posibles estados de una factura para evitar errores de texto.
    | Así, en lugar de escribir 'Anulada' o 'Pendiente' en controladores o vistas,
    | usaremos: Factura::ESTADO_ANULADA, Factura::ESTADO_PENDIENTE, etc.
    */
    public const ESTADO_PENDIENTE = 'Pendiente';
    public const ESTADO_PAGADA = 'Pagada';
    public const ESTADO_ANULADA = 'Anulada';

    protected $fillable = [
        'numero_factura',
        'prefijo',
        'reserva_id',
        'cliente_id',
        'fecha_emision',
        'metodo_pago',
        'subtotal',
        'impuestos',
        'total',
        'cufe',
        'datos_facturacion',
        'datos_empresa',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'datos_facturacion' => 'array',
        'datos_empresa' => 'array',
        'fecha_emision' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    |  RELACIONES
    |--------------------------------------------------------------------------
    */

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'factura_id');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    | Puedes usarlos para verificar el estado de una factura directamente,
    | lo cual es muy útil en controladores o vistas.
    */

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function estaPagada(): bool
    {
        return $this->estado === self::ESTADO_PAGADA;
    }

    public function estaAnulada(): bool
    {
        return $this->estado === self::ESTADO_ANULADA;
    }
}
