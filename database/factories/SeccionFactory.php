<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seccion>
 */
class SeccionFactory extends Factory
{
    /**
     * Catálogo de materias reales agrupadas por área académica, para
     * que 'materia', 'codigo_materia' y 'area_academica' sean
     * coherentes entre sí en vez de tres valores aleatorios sueltos.
     */
    protected array $catalogo = [
        ['materia' => 'Programación I', 'codigo' => 'CS101', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Programación II', 'codigo' => 'CS102', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Estructuras de Datos', 'codigo' => 'CS201', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Base de Datos I', 'codigo' => 'CS210', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Sistemas Operativos', 'codigo' => 'CS310', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Redes de Computadoras', 'codigo' => 'CS320', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Ingeniería de Software', 'codigo' => 'CS330', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Inteligencia Artificial', 'codigo' => 'CS410', 'area' => 'Ingeniería en Sistemas'],
        ['materia' => 'Cálculo I', 'codigo' => 'MAT101', 'area' => 'Ciencias Básicas'],
        ['materia' => 'Cálculo II', 'codigo' => 'MAT102', 'area' => 'Ciencias Básicas'],
        ['materia' => 'Física I', 'codigo' => 'FIS101', 'area' => 'Ciencias Básicas'],
        ['materia' => 'Estadística General', 'codigo' => 'EST201', 'area' => 'Ciencias Básicas'],
        ['materia' => 'Álgebra Lineal', 'codigo' => 'MAT210', 'area' => 'Ciencias Básicas'],
        ['materia' => 'Investigación de Operaciones', 'codigo' => 'IND220', 'area' => 'Ingeniería Industrial'],
        ['materia' => 'Control Estadístico de Calidad', 'codigo' => 'IND310', 'area' => 'Ingeniería Industrial'],
        ['materia' => 'Gestión de Procesos', 'codigo' => 'IND320', 'area' => 'Ingeniería Industrial'],
        ['materia' => 'Contabilidad General', 'codigo' => 'ADM101', 'area' => 'Administración de Empresas'],
        ['materia' => 'Mercadotecnia', 'codigo' => 'ADM210', 'area' => 'Administración de Empresas'],
        ['materia' => 'Finanzas Corporativas', 'codigo' => 'ADM310', 'area' => 'Administración de Empresas'],
        ['materia' => 'Inglés Técnico I', 'codigo' => 'ING101', 'area' => 'Idiomas'],
        ['materia' => 'Inglés Técnico II', 'codigo' => 'ING102', 'area' => 'Idiomas'],
        ['materia' => 'Derecho Empresarial', 'codigo' => 'DER210', 'area' => 'Derecho'],
        ['materia' => 'Diseño Arquitectónico I', 'codigo' => 'ARQ101', 'area' => 'Arquitectura'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $curso = fake()->randomElement($this->catalogo);
        $duracion = fake()->randomElement([1.00, 1.50, 2.00, 3.00]);
        $sesionesPorSemana = fake()->numberBetween(1, 3);

        return [
            'materia' => $curso['materia'],
            'codigo_materia' => $curso['codigo'],
            'id_docente' => Docente::factory(),
            'tipo_sesion' => fake()->randomElement(['matutino', 'vespertino']),
            'area_academica' => $curso['area'],
            'duracion_sesion_horas' => $duracion,
            'horas_semanales_totales' => round($duracion * $sesionesPorSemana, 2),
            'sesiones_por_semana' => $sesionesPorSemana,
            'activa' => true,
        ];
    }

    /**
     * Sección sin titular por defecto todavía.
     */
    public function sinDocente(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_docente' => null,
        ]);
    }

    /**
     * Sección retirada del catálogo activo (no se borra, se desactiva).
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activa' => false,
        ]);
    }

    public function matutina(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_sesion' => 'matutino',
        ]);
    }

    public function vespertina(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_sesion' => 'vespertino',
        ]);
    }
}
