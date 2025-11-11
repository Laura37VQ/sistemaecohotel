<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Reserva;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'nombres',
        'apellidos',
        'documento_identidad',
        'fecha_nacimiento',
        'correo',
        'telefono',
        'direccion',
        'nombre_usuario',
        'contrasena',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function rol()
    {
        return $this->belongsTo(\App\Models\Rol::class, 'rol_id');
    }

    // Relación con reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'usuario_id'); 
    }
}
