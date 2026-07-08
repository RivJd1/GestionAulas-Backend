<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PeriodoAcademico extends Model
{
    use HasFactory;

    // Tabla en plural distinto al que Laravel adivinaría por defecto
    // ("periodo_academicos"), por eso se fija explícitamente.
    protected $table = 'periodos_academicos';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_usuario_creador',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * El Administrador que creó este periodo.
     */
    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }

    /**
     * Todas las asignaciones (sección + aula + docente) de este periodo.
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_periodo');
    }

    /**
     * Todos los bloques de horario generados dentro de este periodo.
     * Ya no es una relación directa: sesiones_horario perdió su
     * columna id_periodo, así que se llega a través de asignaciones.
     */
    public function sesionesHorario(): HasManyThrough
    {
        return $this->hasManyThrough(
            SesionHorario::class,
            Asignacion::class,
            'id_periodo',
            'id_asignacion',
            'id',
            'id'
        );
    }
}
