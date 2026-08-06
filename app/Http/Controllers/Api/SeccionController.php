<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeccionController extends Controller
{
    public function index(): JsonResponse
    {
        $secciones = Seccion::with('docente')->orderBy('materia')->get();

        return response()->json($secciones->map(fn (Seccion $s) => $this->toPayload($s)));
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->has('tipo_sesion')) {
            $request->merge(['tipo_sesion' => strtolower((string) $request->input('tipo_sesion'))]);
        }

        $datos = $request->validate([
            'materia' => ['required', 'string', 'max:150'],
            'codigo_materia' => ['nullable', 'string', 'max:30'],
            'id_docente' => ['nullable', 'integer', 'exists:docentes,id'],
            'tipo_sesion' => ['required', Rule::in(['matutino', 'vespertino'])],
            'area_academica' => ['required', 'string', 'max:100'],
            'duracion_sesion_horas' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'horas_semanales_totales' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'cantidad_alumnos' => ['nullable', 'integer', 'min:0'],
            'sesiones_por_semana' => ['nullable', 'integer', 'min:1'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $datos['sesiones_por_semana'] = $datos['sesiones_por_semana'] ?? 1;
        $datos['activa'] = $datos['activa'] ?? true;

        $seccion = Seccion::create($datos);

        return response()->json([
            'message' => 'Sección creada correctamente.',
            'seccion' => $this->toPayload($seccion),
        ], 201);
    }

    public function show(Seccion $seccion): JsonResponse
    {
        return response()->json($this->toPayload($seccion));
    }

    public function update(Request $request, Seccion $seccion): JsonResponse
    {
        if ($request->has('tipo_sesion')) {
            $request->merge(['tipo_sesion' => strtolower((string) $request->input('tipo_sesion'))]);
        }

        $datos = $request->validate([
            'materia' => ['sometimes', 'required', 'string', 'max:150'],
            'codigo_materia' => ['nullable', 'string', 'max:30'],
            'id_docente' => ['nullable', 'integer', 'exists:docentes,id'],
            'tipo_sesion' => ['sometimes', 'required', Rule::in(['matutino', 'vespertino'])],
            'area_academica' => ['sometimes', 'required', 'string', 'max:100'],
            'duracion_sesion_horas' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99.99'],
            'horas_semanales_totales' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99.99'],
            'cantidad_alumnos' => ['nullable', 'integer', 'min:0'],
            'sesiones_por_semana' => ['sometimes', 'integer', 'min:1'],
            'activa' => ['sometimes', 'boolean'],
        ]);

        $seccion->update($datos);

        return response()->json([
            'message' => 'Sección actualizada correctamente.',
            'seccion' => $this->toPayload($seccion),
        ]);
    }

    public function destroy(Seccion $seccion): JsonResponse
    {
        $seccion->delete();

        return response()->json([
            'message' => 'Sección eliminada correctamente.',
        ]);
    }

    private function toPayload(Seccion $seccion): array
    {
        $seccion->loadMissing('docente');

        return [
            'id' => $seccion->id,
            'materia' => $seccion->materia,
            'codigo_materia' => $seccion->codigo_materia,
            'id_docente' => $seccion->id_docente,
            'docente_nombre' => $seccion->docente?->nombre_completo,
            'tipo_sesion' => $seccion->tipo_sesion,
            'area_academica' => $seccion->area_academica,
            'duracion_sesion_horas' => $seccion->duracion_sesion_horas,
            'horas_semanales_totales' => $seccion->horas_semanales_totales,
            'cantidad_alumnos' => $seccion->cantidad_alumnos,
            'sesiones_por_semana' => $seccion->sesiones_por_semana,
            'activa' => $seccion->activa,
            'created_at' => $seccion->created_at,
            'updated_at' => $seccion->updated_at,
        ];
    }
}
