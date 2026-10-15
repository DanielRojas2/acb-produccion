<?php

namespace App\Modules\Usuarios\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Redirige al panel según el rol del usuario autenticado.
     */
    public function __invoke(): RedirectResponse
    {
        $usuario = auth()->user();

        if ($usuario->esAdmin()) {
            return redirect()->route('admin.inicio');
        }

        return redirect()->route('jurado.inicio');
    }
}