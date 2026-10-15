<?php

namespace App\Modules\Categorias\Controllers;

use App\Modules\Categorias\Models\Categoria;
use App\Modules\Departamentos\Models\Departamento;
use App\Modules\Fases\Models\Fase;
use Illuminate\Contracts\View\View;

class ConfiguracionController
{
    public function index(): View
    {
        return view('admin.configuracion', [
            'categorias' => Categoria::with('categoriaParametros.parametro')->orderBy('id')->get(),
            'departamentos' => Departamento::withCount('fases')->orderBy('id')->get(),
            'fases' => Fase::with('departamento')->orderBy('fase')->orderBy('departamento_id')->get(),
        ]);
    }
}