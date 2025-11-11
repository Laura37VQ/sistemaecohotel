<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::check()) {
            // Redirigir según el rol
            $rol = Auth::user()->rol_id;
            if ($rol == 1) return redirect()->route('dashboard.admin');
            if ($rol == 2) return redirect()->route('dashboard.cliente');
            if ($rol == 3) return redirect()->route('dashboard.recepcionista');
        }

        return $next($request);
    }
}
