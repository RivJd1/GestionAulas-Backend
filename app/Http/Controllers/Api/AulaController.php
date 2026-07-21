<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Aula;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AulaController extends Controller
{
    /**
     * GET /api/aulas
     * Lista todas las aulas.
     */
    public function index(): JsonResponse
    {
        return response()->json(Aula::orderBy('edificio')->orderBy('nombre')->get());
    }

    /**
     * POST /api/aulas
     * Crea un aula nueva.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                // nombre + edificio deben ser únicos juntos (regla del negocio, ver README)
                Rule::unique('aulas')->where(
                    fn ($query) => $query->where('edificio', $request->input('edificio'))
                ),
            ],
            'edificio' => ['required', 'string', 'max:100'],
            'piso' => ['required', 'string', 'max:20'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'capacidad_maxima' => ['required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', Rule::in(['disponible', 'mantenimiento'])],
        ]);

        $datos['estado'] = $datos['estado'] ?? 'disponible';

        $aula = Aula::create($datos);

        return response()->json([
            'message' => 'Aula creada correctamente.',
            'aula' => $aula,
        ], 201);
    }

    /**
     * GET /api/aulas/{aula}
     * Muestra una sola aula. Laravel resuelve el {aula} automáticamente
     * buscando por id gracias a Route Model Binding.
     */
    public function show(Aula $aula): JsonResponse
    {
        return response()->json($aula);
    }

    /**
     * PUT/PATCH /api/aulas/{aula}
     * Actualiza una aula. Con "sometimes" acepta actualizaciones parciales:
     * si no envías un campo, no lo exige ni lo toca.
     */
    public function update(Request $request, Aula $aula): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('aulas')->ignore($aula->id)->where(
                    fn ($query) => $query->where('edificio', $request->input('edificio', $aula->edificio))
                ),
            ],
            'edificio' => ['sometimes', 'required', 'string', 'max:100'],
            'piso' => ['sometimes', 'required', 'string', 'max:20'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'capacidad_maxima' => ['sometimes', 'required', 'integer', 'min:1'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::in(['disponible', 'mantenimiento'])],
        ]);

        $aula->update($datos);

        return response()->json([
            'message' => 'Aula actualizada correctamente.',
            'aula' => $aula,
        ]);
    }

    /**
     * DELETE /api/aulas/{aula}
     * Elimina una aula.
     */
    public function destroy(Aula $aula): JsonResponse
    {
        $aula->delete();

        return response()->json([
            'message' => 'Aula eliminada correctamente.',
        ]);
    }
}
