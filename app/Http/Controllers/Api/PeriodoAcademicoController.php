<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodoAcademicoController extends Controller
{
    /**
     * GET /api/periodos-academicos
     * Lista todos los periodos.
     */
    public function index(): JsonResponse
    {
        return response()->json(PeriodoAcademico::all());
    }

    /**
     * POST /api/periodos-academicos
     * Crea un periodo nuevo.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:periodos_academicos,nombre'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'CERRADO'])],
            'id_usuario_creador' => ['required', 'integer', 'exists:users,id'],
        ]);

        $datos['estado'] = $datos['estado'] ?? 'ACTIVO';

        $periodo = PeriodoAcademico::create($datos);

        return response()->json([
            'message' => 'Periodo académico creado correctamente.',
            'periodo' => $periodo,
        ], 201);
    }

    /**
     * GET /api/periodos-academicos/{id}
     * Muestra un solo periodo.
     */
    public function show($id): JsonResponse
    {
        $periodo = PeriodoAcademico::find($id);

        if (! $periodo) {
            return response()->json([
                'message' => 'El periodo académico que intentas consultar no existe.',
            ], 404);
        }

        return response()->json($periodo);
    }

    /**
     * PUT/PATCH /api/periodos-academicos/{id}
     * Actualiza un periodo. Acepta actualizaciones parciales.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $periodo = PeriodoAcademico::find($id);

        if (! $periodo) {
            return response()->json([
                'message' => 'El periodo académico que intentas editar no existe.',
            ], 404);
        }

        $datos = $request->validate([
            'nombre' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('periodos_academicos', 'nombre')->ignore($periodo->id),
            ],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['sometimes', 'required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['sometimes', Rule::in(['ACTIVO', 'CERRADO'])],
            'id_usuario_creador' => ['sometimes', 'required', 'integer', 'exists:usuarios,id'],
        ]);

        $periodo->update($datos);

        return response()->json([
            'message' => 'Periodo académico actualizado correctamente.',
            'periodo' => $periodo,
        ]);
    }

    /**
     * DELETE /api/periodos-academicos/{id}
     * Elimina un periodo.
     */
    public function destroy($id): JsonResponse
    {
        $periodo = PeriodoAcademico::find($id);

        if (! $periodo) {
            return response()->json([
                'message' => 'El periodo académico que intentas eliminar no existe.',
            ], 404);
        }

        $periodo->delete();

        return response()->json([
            'message' => 'Periodo académico eliminado correctamente.',
        ]);
    }
}
