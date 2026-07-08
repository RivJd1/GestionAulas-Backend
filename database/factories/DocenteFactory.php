<?php

namespace Database\Factories;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Docente>
 */
class DocenteFactory extends Factory
{
    /**
     * Departamentos académicos usados como catálogo simple.
     * El campo es informativo y opcional: un docente puede dar clases
     * fuera de su departamento de origen.
     */
    protected array $departamentos = [
        'Ingeniería en Sistemas',
        'Ingeniería Industrial',
        'Ciencias Básicas',
        'Idiomas',
        'Administración de Empresas',
        'Arquitectura',
        'Derecho',
    ];

    /**
     * Especialidades usadas como catálogo simple.
     */
    protected array $especialidades = [
        'Desarrollo de Software',
        'Redes y Telecomunicaciones',
        'Bases de Datos',
        'Inteligencia Artificial',
        'Matemáticas Aplicadas',
        'Gestión de Proyectos',
        'Estadística',
        'Sistemas Operativos',
        'Electrónica y Microcontroladores',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('es_ES');
        $nombre = $faker->firstName() . ' ' . $faker->lastName() . ' ' . $faker->lastName();

        // Correo institucional derivado del nombre, con sufijo numérico
        // único para evitar choques cuando se repiten nombres/apellidos.
        $usuarioCorreo = Str::of($nombre)
            ->ascii()
            ->lower()
            ->replace(' ', '.')
            ->toString();

        return [
            'nombre_completo' => $nombre,
            'correo_institucional' => $this->faker->unique()->numerify($usuarioCorreo . '##') . '@unicah.edu.hn',
            'telefono' => '9' . $this->faker->numerify('###-####'),
            'departamento' => $faker->optional(0.75)->randomElement($this->departamentos),
            'especialidad' => $faker->optional(0.85)->randomElement($this->especialidades),
            'estado' => $faker->randomElement(['activo', 'activo', 'activo', 'activo', 'inactivo', 'licencia']),
        ];
    }

    /**
     * Docente activo (estado por defecto en la mayoría de casos).
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'activo',
        ]);
    }

    /**
     * Docente inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'inactivo',
        ]);
    }

    /**
     * Docente en licencia (ej. maternidad u otro retiro temporal).
     * Sigue existiendo y puede seguir referenciado en secciones o
     * asignaciones antiguas: el backend es responsable de avisar y
     * de gestionar la reasignación, esto no se hace solo.
     */
    public function enLicencia(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'licencia',
        ]);
    }
}
