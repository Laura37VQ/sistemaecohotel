<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Habitacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'habitaciones';

    protected $fillable = [
        'numero_habitacion',
        'tipo',
        'capacidad_personas',
        'descripcion',
        'precio',
        'estado',
        'foto',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'habitacion_id');
    }
}
