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
        return response()->json(Seccion::with('docente')->orderBy('materia')->get());
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
            'seccion' => $seccion,
        ], 201);
    }


    public function show(Seccion $seccion): JsonResponse
    {
        return response()->json($seccion->load('docente'));
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
            'seccion' => $seccion,
        ]);
    }

    public function destroy(Seccion $seccion): JsonResponse
    {
        $seccion->delete();

        return response()->json([
            'message' => 'Sección eliminada correctamente.',
        ]);
    }
}
