<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\MarcadoController;
use App\Http\Controllers\Api\ReconocimientoController;
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

    // Marcados
    Route::get('/marcados',      [MarcadoController::class, 'index']);
    Route::post('/marcados',     [MarcadoController::class, 'store']);
    Route::get('/marcados/{id}', [MarcadoController::class, 'show']);

    // Reconocimiento facial
    Route::prefix('reconocimiento')->group(function () {
        Route::post('/verificar',           [ReconocimientoController::class, 'verificar']);
        Route::post('/registrar-embedding', [ReconocimientoController::class, 'registrarEmbedding']);
        Route::get('/estado/{docenteId}',   [ReconocimientoController::class, 'estado']);
    });
});