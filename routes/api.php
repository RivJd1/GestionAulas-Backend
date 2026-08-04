<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SesionHorarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AulaController;
use App\Http\Controllers\Api\SeccionController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\AsignacionController;
use App\Http\Controllers\Api\PeriodoAcademicoController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('aulas', AulaController::class);
Route::apiResource('secciones', SeccionController::class);
Route::apiResource('docentes', DocenteController::class);
Route::apiResource('asignaciones', AsignacionController::class);
Route::post('asignaciones/{asignacion}', [AsignacionController::class, 'update']);
Route::apiResource('periodos-academicos', PeriodoAcademicoController::class);
Route::apiResource('sesiones-horario', SesionHorarioController::class);
});
