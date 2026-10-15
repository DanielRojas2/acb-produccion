<?php

use App\Modules\Calificaciones\Controllers\AdminCalificacionesController;
use App\Modules\Calificaciones\Controllers\CalificacionJuradoController;
use App\Modules\Calificaciones\Controllers\CorreccionCalificacionController;
use App\Modules\Categorias\Controllers\ConfiguracionController;
use App\Modules\Importaciones\Controllers\ImportacionController;
use App\Modules\Inscripciones\Controllers\InscripcionController;
use App\Modules\Jurados\Controllers\JuradoController;
use App\Modules\Participantes\Controllers\ParticipanteController;
use App\Modules\Presentaciones\Controllers\PresentacionController;
use App\Modules\Usuarios\Controllers\DashboardController;
use App\Modules\Usuarios\Controllers\InicioController;
use App\Modules\Usuarios\Controllers\UsuariosController;
use App\Support\Enums\RolEnum;
use Illuminate\Support\Facades\Route;

// Página pública
Route::get('/', [InicioController::class, 'publico'])->name('inicio');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Panel de administración
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', sprintf('rol:%s', RolEnum::ADMIN->value)])->group(function () {
    Route::get('/inicio', [InicioController::class, 'admin'])->name('inicio');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');

    Route::resource('usuarios', UsuariosController::class)
        ->except(['show'])
        ->names('usuarios');

    Route::resource('participantes', ParticipanteController::class)
        ->except(['show'])
        ->names('participantes');

    Route::resource('inscripciones', InscripcionController::class)
        ->parameters(['inscripciones' => 'inscripcion'])
        ->except(['show'])
        ->names('inscripciones');

    Route::prefix('importaciones')->name('importaciones.')->group(function () {
        Route::get('/', [ImportacionController::class, 'index'])->name('index');
        Route::post('/importar', [ImportacionController::class, 'importar'])->name('importar');
        Route::get('/plantilla', [ImportacionController::class, 'plantilla'])->name('plantilla');
    });

    Route::prefix('presentaciones')->name('presentaciones.')->group(function () {
        Route::get('/', [PresentacionController::class, 'index'])->name('index');
        Route::get('/escenario', [PresentacionController::class, 'escenario'])->name('escenario');
        Route::post('/{presentacion}/transicion', [PresentacionController::class, 'transicion'])->name('transicion');
    });

    Route::prefix('jurados')->name('jurados.')->group(function () {
        Route::get('/', [JuradoController::class, 'index'])->name('index');
        Route::post('/asignar', [JuradoController::class, 'asignar'])->name('asignar');
        Route::post('/auto-asignar', [JuradoController::class, 'autoAsignar'])->name('auto-asignar');
        Route::post('/remover', [JuradoController::class, 'remover'])->name('remover');
        Route::get('/secreto', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'index'])->name('secreto');
        Route::post('/secreto/designar', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'designar'])->name('secreto.designar');
    });

    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::post('/{categoria}/preparar-podio', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'prepararPodio'])->name('preparar-podio');
        Route::post('/{categoria}/revelar', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'revelar'])->name('revelar');
        Route::post('/{categoria}/podio-paso', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'podioPaso'])->name('podio-paso');
        Route::post('/{categoria}/ocultar', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'ocultar'])->name('ocultar');
        Route::post('/{categoria}/finalizar', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'finalizar'])->name('finalizar');
        Route::post('/{categoria}/reabrir', [\App\Modules\Jurados\Controllers\AdminJuradoSecretoController::class, 'reabrir'])->name('reabrir');
    });

    Route::prefix('calificaciones')->name('calificaciones.')->group(function () {
        Route::get('/presentacion/{presentacion}', [AdminCalificacionesController::class, 'presentacion'])->name('presentacion');
        Route::get('/historial/{calificacion}', [AdminCalificacionesController::class, 'historial'])->name('historial');
    });

    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [\App\Modules\Auditoria\Controllers\AuditoriaController::class, 'index'])->name('index');
        Route::get('/{historial}', [\App\Modules\Auditoria\Controllers\AuditoriaController::class, 'show'])->name('show');
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [\App\Modules\Reportes\Controllers\ReportesController::class, 'index'])->name('index');
        Route::get('/resultados', [\App\Modules\Reportes\Controllers\ReportesController::class, 'resultados'])->name('resultados');
        Route::get('/acta', [\App\Modules\Reportes\Controllers\ReportesController::class, 'acta'])->name('acta');
        Route::get('/sabana', [\App\Modules\Reportes\Controllers\ReportesController::class, 'sabana'])->name('sabana');
        Route::get('/exportar/resultados', [\App\Modules\Reportes\Controllers\ReportesController::class, 'exportarResultadosCsv'])->name('exportar.resultados');
        Route::get('/exportar/sabana', [\App\Modules\Reportes\Controllers\ReportesController::class, 'exportarSabanaCsv'])->name('exportar.sabana');
    });
});

// Panel de jurado
Route::prefix('jurado')->name('jurado.')->middleware(['auth', 'verified', sprintf('rol:%s', RolEnum::JURADO->value)])->group(function () {
    Route::get('/inicio', [InicioController::class, 'jurado'])->name('inicio');
    Route::get('/presentaciones/actual', [PresentacionController::class, 'escenario'])->name('presentaciones.actual');

    Route::prefix('calificaciones')->name('calificaciones.')->group(function () {
        Route::get('/presentaciones/{presentacion}/calificar', [CalificacionJuradoController::class, 'evaluar'])->name('evaluar');
        Route::post('/presentaciones/{presentacion}/calificar', [CalificacionJuradoController::class, 'guardar'])->name('guardar');
        Route::get('/presentaciones/{presentacion}/corregir', [CorreccionCalificacionController::class, 'formulario'])->name('corregir.form');
        Route::post('/{calificacion}/corregir', [CorreccionCalificacionController::class, 'corregir'])->name('corregir');
    });
});

// Cronómetro: accesible para admin autorizado y jurado controlador (la autorización se valida con el Gate)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/presentaciones/{presentacion}/cronometro/iniciar', [PresentacionController::class, 'cronometroIniciar'])
        ->name('presentaciones.cronometro.iniciar');
    Route::post('/presentaciones/{presentacion}/cronometro/detener', [PresentacionController::class, 'cronometroDetener'])
        ->name('presentaciones.cronometro.detener');

    // Mesa de Jurados / Delegado de Mesa
    Route::prefix('mesa')->name('mesa.')->group(function () {
        Route::post('/categorias/{categoria}/seleccionar', [\App\Modules\Jurados\Controllers\MesaJuradoController::class, 'seleccionarCategoria'])
            ->name('categorias.seleccionar');
        Route::post('/categorias/{categoria}/cerrar', [\App\Modules\Jurados\Controllers\MesaJuradoController::class, 'cerrarCalificaciones'])
            ->name('categorias.cerrar');
        Route::post('/categorias/{categoria}/revelar', [\App\Modules\Jurados\Controllers\MesaJuradoController::class, 'revelarGanador'])
            ->name('categorias.revelar');
        Route::post('/categorias/{categoria}/podio-paso', [\App\Modules\Jurados\Controllers\MesaJuradoController::class, 'podioPaso'])
            ->name('categorias.podio-paso');
        Route::post('/presentaciones/{presentacion}/transicion', [\App\Modules\Jurados\Controllers\MesaJuradoController::class, 'transicion'])
            ->name('presentaciones.transicion');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';