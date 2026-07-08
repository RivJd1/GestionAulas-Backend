<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 150);
            $table->string('correo_institucional', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('especialidad', 150)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'licencia'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
