<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    // Relación con el rol
    public function rol()
    {
        return $this->belongsTo(\App\Models\Rol::class, 'rol_id');
    }

    // Relación con reservas
    public function reservas()
    {
        return $this->hasMany(\App\Models\Reserva::class, 'usuario_id');
    }

    // Relación con facturas
    public function facturas()
    {
        return $this->hasMany(\App\Models\Factura::class, 'cliente_id');
    }
}
