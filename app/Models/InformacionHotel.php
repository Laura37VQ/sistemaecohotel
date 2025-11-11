<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InformacionHotel extends Model
{
    use HasFactory;

    protected $table = 'informacion_hotel';

    protected $fillable = [
        'nombre',
        'nit',
        'regimen_tributario',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'pais',
        'actividad_economica',
        'logo',
        'mision',
        'vision',
    ];

    /**
     * Accesor para obtener la URL pública del logo.
     * Si no hay logo, devuelve null o una imagen por defecto.
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $this->logo))) {
            return asset($this->logo);
        }

        // Imagen por defecto si no hay logo
        return asset('images/default-logo.png');
    }
}
