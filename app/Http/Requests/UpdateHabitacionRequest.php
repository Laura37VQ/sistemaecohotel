<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHabitacionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->rol_id == 1;
    }

    public function rules()
    {
        $id = $this->route('habitacion') ? $this->route('habitacion')->id : null;

        return [
            'numero_habitacion'   => 'required|string|max:10|unique:habitaciones,numero_habitacion,'.$id,
            'tipo'                => 'required|in:Individual,Doble,Suite,Familiar',
            'capacidad_personas'  => 'required|integer|min:1',
            'precio'              => 'required|numeric|min:0',
            'estado'              => 'required|in:Disponible,Ocupada,Mantenimiento',
            'descripcion'         => 'nullable|string',
            'foto'                => 'nullable|string',
        ];
    }
}
