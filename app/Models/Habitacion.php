<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
  Este modelo representa la tabla "habitaciones".
  Aquí definimos qué campos se pueden llenar y activamos
  SoftDeletes, que permite desactivar una habitación sin eliminarla.
*/
class Habitacion extends Model
{
    use HasFactory, SoftDeletes; // Permite desactivar y luego restaurar la habitación

    protected $table = 'habitaciones';

    protected $fillable = [
        'numero_habitacion',   // Ejemplo: 101, 202, etc.
        'tipo',                // Individual, Doble, Suite, Familiar
        'capacidad_personas',  // Cantidad máxima de huéspedes
        'descripcion',         // Texto descriptivo
        'precio',              // Precio por noche
        'estado',              // Disponible, Ocupada, Mantenimiento
        'foto',                // Ruta de la imagen almacenada
    ];

    /*
      Relación: una habitación puede tener varias reservas.
    */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'habitacion_id');
    }
}
