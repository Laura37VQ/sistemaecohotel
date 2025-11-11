<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleFactura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detalles_facturas';

    protected $fillable = [
        'factura_id',
        'servicio_id',
        'codigo',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'impuesto',
        'total',
    ];

    //  Relaciones
    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    //  Accesor opcional (por si en el futuro necesitas el total calculado al vuelo)
    public function getTotalCalculadoAttribute()
    {
        return $this->cantidad * $this->precio_unitario * 1.19;
    }
}
