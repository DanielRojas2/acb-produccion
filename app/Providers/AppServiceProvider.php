<?php

namespace App\Providers;

use App\Modules\Jurados\Models\JuradoPresentacion;
use App\Modules\Presentaciones\Models\Presentacion;
use App\Modules\Usuarios\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('controlarCronometro', function (Usuario $usuario, Presentacion $presentacion) {
            if ($usuario->puedeControlarTiempo()) {
                return true;
            }

            return JuradoPresentacion::where('id_presentacion', $presentacion->id)
                ->where('id_usuario', $usuario->id)
                ->where('es_controlador', true)
                ->exists();
        });

        Gate::define('asignarJurados', function (Usuario $usuario) {
            return $usuario->esAdmin();
        });

        Gate::define('controlarMesa', function (Usuario $usuario) {
            return $usuario->esAdmin()
                || $usuario->puedeControlarTiempo()
                || JuradoPresentacion::where('id_usuario', $usuario->id)->where('es_controlador', true)->exists();
        });
    }
}