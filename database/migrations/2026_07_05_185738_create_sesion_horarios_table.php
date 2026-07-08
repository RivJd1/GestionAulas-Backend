<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bloques semanales concretos (día, hora) de una asignación.
     * 'generado_automaticamente' distingue los bloques creados por
     * la función de replicación automática de los que el coordinador
     * arrastró manualmente en el calendario.
     */
    public function up(): void
    {
        Schema::create('sesiones_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asignacion')
                ->constrained('asignaciones')
                ->cascadeOnDelete();
            $table->enum('dia', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('generado_automaticamente')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_horario');
    }
};
