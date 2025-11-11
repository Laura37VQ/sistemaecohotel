<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InformacionHotel;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class InformacionHotelController extends Controller
{
    public function index()
    {
        $info = InformacionHotel::first();
        return Inertia::render('Admin/InformacionHotel/Index', [
            'info' => $info
        ]);
    }

    public function edit()
    {
        $info = InformacionHotel::first();
        return Inertia::render('Admin/InformacionHotel/Form', [
            'info' => $info
        ]);
    }

    public function save(Request $request)
    {
        $info = InformacionHotel::first();

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'required|string|max:50',
            'regimen_tributario' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'ciudad' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'actividad_economica' => 'required|string|max:255',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Manejo del logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;

            // Borrar logo anterior
            if ($info && $info->logo && Storage::disk('public')->exists($info->logo)) {
                Storage::disk('public')->delete($info->logo);
            }
        } else if ($info) {
            // Mantener logo existente
            $data['logo'] = $info->logo;
        }

        if ($info) {
            $info->update($data);
            $msg = 'Información actualizada correctamente';
        } else {
            InformacionHotel::create($data);
            $msg = 'Información registrada correctamente';
        }

        return redirect()->route('admin.informacion-hotel.index')->with('success', $msg);
    }
}
