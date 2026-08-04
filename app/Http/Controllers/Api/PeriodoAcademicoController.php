<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodoAcademicoController extends Controller
{
    /**
     * GET /api/periodos-academicos
     * Lista todos los periodos.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            PeriodoAcademico::orderByDesc('created_at')->get()
        );
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
            'estado' => ['nullable', Rule::in(['ACTIVO', 'CERRADO', 'activo', 'cerrado'])],
            'id_usuario_creador' => ['required', 'integer', 'exists:users,id'],
        ]);

        $datos['estado'] = strtolower($datos['estado'] ?? 'activo');

        $periodo = PeriodoAcademico::create($datos);

        return response()->json([
            'message' => 'Periodo académico creado correctamente.',
            'periodo' => $periodo,
        ], 201);
    }

    /**
     * GET /api/periodos-academicos/{periodo}
     * Muestra un solo periodo.
     */
    public function show(PeriodoAcademico $periodo): JsonResponse
    {
        return response()->json($periodo);
    }

    /**
     * PUT/PATCH /api/periodos-academicos/{periodo}
     * Actualiza un periodo. Acepta actualizaciones parciales.
     */
    public function update(Request $request, PeriodoAcademico $periodo): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('periodos_academicos', 'nombre')->ignore($periodo->id),
            ],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['sometimes', 'required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['sometimes', Rule::in(['ACTIVO', 'CERRADO', 'activo', 'cerrado'])],
            'id_usuario_creador' => ['sometimes', 'required', 'integer', 'exists:users,id'],
        ]);

        return DB::transaction(function () use ($datos, $periodo): JsonResponse {
            if (array_key_exists('estado', $datos)) {
                $estado = strtolower($datos['estado']);
                $datos['estado'] = $estado;

                if ($estado === 'activo') {
                    PeriodoAcademico::query()
                        ->where('id', '!=', $periodo->id)
                        ->whereRaw('LOWER(estado) = ?', ['activo'])
                        ->update(['estado' => 'cerrado']);
                }
            }

            $periodo->update($datos);

            return response()->json([
                'message' => 'Periodo académico actualizado correctamente.',
                'periodo' => $periodo->refresh(),
            ]);
        });
    }

    /**
     * POST /api/periodos-academicos/{periodo}/activar
     * Activa un periodo y cierra cualquier otro que esté activo.
     */
    public function activate(PeriodoAcademico $periodo): JsonResponse
    {
        return DB::transaction(function () use ($periodo): JsonResponse {
            PeriodoAcademico::query()
                ->where('id', '!=', $periodo->id)
                ->whereRaw('LOWER(estado) = ?', ['activo'])
                ->update(['estado' => 'cerrado']);

            $periodo->estado = 'activo';
            $periodo->save();

            return response()->json([
                'message' => 'Periodo académico actualizado correctamente.',
                'periodo' => $periodo->refresh(),
            ]);
        });
    }

    /**
     * DELETE /api/periodos-academicos/{periodo}
     * Elimina un periodo.
     */
    public function destroy(PeriodoAcademico $periodo): JsonResponse
    {
        if (strtolower($periodo->estado) === 'activo') {
            return response()->json([
                'message' => 'No se puede eliminar un periodo activo.',
            ], 422);
        }

        return DB::transaction(function () use ($periodo): JsonResponse {
            $asignacionIds = DB::table('asignaciones')
                ->where('id_periodo', $periodo->id)
                ->pluck('id');

            if ($asignacionIds->isNotEmpty()) {
                DB::table('sesiones_horario')
                    ->whereIn('id_asignacion', $asignacionIds->all())
                    ->delete();

                DB::table('asignaciones')
                    ->where('id_periodo', $periodo->id)
                    ->delete();
            }

            DB::table('periodos_academicos')
                ->where('id', $periodo->id)
                ->delete();

            return response()->json([
                'message' => 'Periodo académico eliminado correctamente.',
            ]);
        });
    }
}