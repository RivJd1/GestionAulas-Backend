<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SesionHorario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SesionHorarioController extends Controller
{
    /**
     * Valores válidos para el campo dia.
     */
    private const DIAS_VALIDOS = [
        'LUNES',
        'MARTES',
        'MIERCOLES',
        'JUEVES',
        'VIERNES',
        'SABADO',
    ];

    /**
     * GET /api/sesiones-horario
     * Lista todas las sesiones.
     */
    public function index(): JsonResponse
    {
        return response()->json(SesionHorario::all());
    }

    /**
     * POST /api/sesiones-horario
     * Crea una sesión nueva.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $this->validarDatos($request);

        $sesion = SesionHorario::create($datos);

        return response()->json([
            'message' => 'Sesión de horario creada correctamente.',
            'sesion' => $sesion->fresh(),
        ], 201);
    }

    /**
     * GET /api/sesiones-horario/{id}
     * Muestra una sola sesión.
     */
    public function show(int $id): JsonResponse
    {
        $sesionHorario = SesionHorario::find($id);

        if (! $sesionHorario) {
            return response()->json([
                'message' => 'La sesión de horario que intentas consultar no existe.',
            ], 404);
        }

        return response()->json($sesionHorario);
    }

    /**
     * PUT/PATCH /api/sesiones-horario/{id}
     * Actualiza una sesión. Acepta actualizaciones parciales.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $sesionHorario = SesionHorario::find($id);

        if (! $sesionHorario) {
            return response()->json([
                'message' => 'La sesión de horario que intentas editar no existe.',
            ], 404);
        }

        $datos = $this->validarDatos($request, true);

        $sesionHorario->update($datos);

        return response()->json([
            'message' => 'Sesión de horario actualizada correctamente.',
            'sesion' => $sesionHorario->fresh(),
        ]);
    }

    /**
     * DELETE /api/sesiones-horario/{id}
     * Elimina una sesión.
     */
    public function destroy(int $id): JsonResponse
    {
        $sesionHorario = SesionHorario::find($id);

        if (! $sesionHorario) {
            return response()->json([
                'message' => 'La sesión de horario que intentas eliminar no existe.',
            ], 404);
        }

        $sesionHorario->delete();

        return response()->json([
            'message' => 'Sesión de horario eliminada correctamente.',
        ]);
    }

    /**
     * Valida y normaliza datos de entrada.
     */
    private function validarDatos(Request $request, bool $parcial = false): array
    {
        if ($request->has('dia')) {
            $request->merge([
                'dia' => strtoupper((string) $request->input('dia')),
            ]);
        }

        $reglas = [
            'id_asignacion' => [$parcial ? 'sometimes' : 'required', 'integer', 'exists:asignaciones,id'],
            'dia' => [$parcial ? 'sometimes' : 'required', Rule::in(self::DIAS_VALIDOS)],
            'hora_inicio' => [$parcial ? 'sometimes' : 'required', 'date_format:H:i'],
            'hora_fin' => [$parcial ? 'sometimes' : 'required', 'date_format:H:i'],
            'generado_automaticamente' => [$parcial ? 'sometimes' : 'nullable', 'boolean'],
        ];

        $datos = $request->validate($reglas);

        if (array_key_exists('hora_inicio', $datos) && array_key_exists('hora_fin', $datos)) {
            if ($datos['hora_fin'] <= $datos['hora_inicio']) {
                return throw \Illuminate\Validation\ValidationException::withMessages([
                    'hora_fin' => ['La hora fin debe ser posterior a la hora inicio.'],
                ]);
            }
        }

        if (! array_key_exists('generado_automaticamente', $datos) && ! $parcial) {
            $datos['generado_automaticamente'] = false;
        }

        return $datos;
    }
}
