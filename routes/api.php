<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SesionHorarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AulaController;
use App\Http\Controllers\Api\SeccionController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\AsignacionController;
use App\Http\Controllers\Api\PeriodoAcademicoController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});


Route::apiResource('aulas', AulaController::class);
    Route::apiResource('secciones', SeccionController::class);
    Route::apiResource('docentes', DocenteController::class);
    Route::apiResource('asignaciones', AsignacionController::class);
    Route::post('asignaciones/{asignacion}', [AsignacionController::class, 'update']);
    Route::get('periodos-academicos', [PeriodoAcademicoController::class, 'index']);
    Route::post('periodos-academicos', [PeriodoAcademicoController::class, 'store']);
    Route::get('periodos-academicos/{periodo}', [PeriodoAcademicoController::class, 'show']);
    Route::match(['put', 'patch'], 'periodos-academicos/{periodo}', [PeriodoAcademicoController::class, 'update']);
    Route::delete('periodos-academicos/{periodo}', [PeriodoAcademicoController::class, 'destroy']);
    Route::post('periodos-academicos/{periodo}/activar', [PeriodoAcademicoController::class, 'activate']);
    Route::apiResource('sesiones-horario', SesionHorarioController::class);


    Route::get('dashboard', [DashboardController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {

});
