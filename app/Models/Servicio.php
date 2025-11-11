<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios'; 

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'foto',
        'estado',
    ];


    public function categoriaServicio()
    {
        return $this->belongsTo(CategoriaServicio::class, 'categoria_id');
    }

    /**

     */
    public function getFotoUrlAttribute()
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
