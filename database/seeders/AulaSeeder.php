<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Aula::factory()->count(10)->create();

        Aula::factory()->laboratorio()->count(2)->create();

        // Un aula bloqueada por mantenimiento, para probar que el
        // Kanban la muestre visualmente deshabilitada.
        Aula::factory()->enMantenimiento()->create([
            'nombre' => 'Aula 305',
            'edificio' => 'Edificio de Ingeniería',
        ]);

        // Demuestra que el mismo número de salón puede repetirse en
        // edificios distintos: el unique es sobre (nombre, edificio)
        // en conjunto, no sobre nombre por sí solo. Se usa un número
        // fuera del rango 101-112 que ya genera el contador automático
        // de arriba, para no arriesgarse a chocar con él por azar.
        Aula::factory()->create([
            'nombre' => 'Aula 250',
            'edificio' => 'Edificio A',
        ]);
        Aula::factory()->create([
            'nombre' => 'Aula 250',
            'edificio' => 'Edificio B',
        ]);
    }
}
