<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaServicio extends Model
{
    use SoftDeletes;

    protected $table = 'categoria_servicios'; // 

    protected $fillable = [
        'nombre_categoria',
        'descripcion',
        'estado', // 
    ];

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'categoria_id');
    }
}
