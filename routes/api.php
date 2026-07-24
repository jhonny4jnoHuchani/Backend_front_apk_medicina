<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\MarcadoController;
use App\Http\Controllers\Api\ReconocimientoController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\ParaleloController;
use App\Http\Controllers\Api\AsignacionController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\HorarioController;

use Illuminate\Support\Facades\Route;

// ── Rutas públicas (sin token) ──────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Rutas protegidas (requieren token Sanctum) ──────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout',           [AuthController::class, 'logout']);
        Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword']);
        Route::get('/perfil',            [AuthController::class, 'perfil']);
        Route::patch('/reset-dispositivo/{id}', [AuthController::class, 'resetDispositivo']);
    });

    // Docentes
    Route::apiResource('docentes', DocenteController::class);
    Route::patch('/docentes/{id}/reactivar', [DocenteController::class, 'reactivar']);



    // Reconocimiento facial
    Route::prefix('reconocimiento')->group(function () {
        Route::post('/verificar',           [ReconocimientoController::class, 'verificar']);
        Route::post('/registrar-embedding', [ReconocimientoController::class, 'registrarEmbedding']);
        Route::get('/estado/{docenteId}',   [ReconocimientoController::class, 'estado']);
    });

    // Recursos Materias
    Route::apiResource('materias', MateriaController::class);

    // Recursos Paralelos
    Route::apiResource('paralelos', ParaleloController::class);


    Route::prefix('asignaciones')->group(function () {
        Route::get('/', [AsignacionController::class, 'index']);         // Listar
        Route::post('/', [AsignacionController::class, 'store']);        // Asignar materia a paralelo
        Route::put('{id}/asignar-docente', [AsignacionController::class, 'asignarDocente']);  // Poner docente
        Route::delete('{id}/quitar-docente', [AsignacionController::class, 'quitarDocente']); // Sacar docente
        Route::delete('{id}', [AsignacionController::class, 'destroy']); // Eliminar asignación
    });
//ubicaciones
    Route::prefix('ubicaciones')->group(function () {
        Route::get('/', [UbicacionController::class, 'index']);                    // Listar
        Route::post('/crear', [UbicacionController::class, 'store']);              // Crear
        Route::get('{id}/ver', [UbicacionController::class, 'show']);              // Ver una
        Route::put('{id}/actualizar', [UbicacionController::class, 'update']);     // Editar
        Route::delete('{id}/eliminar', [UbicacionController::class, 'destroy']);   // Desactivar
    });

    //hroarios
    Route::prefix('horarios')->group(function () {
        Route::get('/', [HorarioController::class, 'index']);              // Listar
        Route::get('/hoy', [HorarioController::class, 'hoy']);             // Horarios del docente para hoy
        Route::post('/crear', [HorarioController::class, 'store']);        // Crear
        Route::get('{id}/ver', [HorarioController::class, 'show']);        // Ver uno
        Route::put('{id}/actualizar', [HorarioController::class, 'update']); // Editar
        Route::delete('{id}/eliminar', [HorarioController::class, 'destroy']); // Desactivar
    });

    //marcados de entrada y salida validando ubicacion con la geocerca poligonal
    // Marcados de entrada y salida validando ubicación con la geocerca poligonal
    Route::prefix('marcados')->group(function () {
        Route::get('/historial', [MarcadoController::class, 'historial']);
        Route::get('/hoy', [MarcadoController::class, 'hoy']);
        Route::post('/entrada', [MarcadoController::class, 'entrada']);
        Route::post('/salida', [MarcadoController::class, 'salida']);
        Route::post('/sync-offline', [MarcadoController::class, 'syncOffline']);
    });
});