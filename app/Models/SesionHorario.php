<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class SesionHorario extends Model
{
    use HasFactory;

    // Laravel pluralizaría "sesion_horario" como "sesion_horarios"
    // (orden distinto), por eso se fija la tabla explícitamente.
    protected $table = 'sesiones_horario';

    protected $fillable = [
        'id_asignacion',
        'dia',
        'hora_inicio',
        'hora_fin',
        'generado_automaticamente',
    ];

    protected $casts = [
        'generado_automaticamente' => 'boolean',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    /**
     * El periodo académico se obtiene indirectamente a través de la
     * asignación (esta tabla ya no tiene id_periodo propio).
     */
    public function periodo(): HasOneThrough
    {
        return $this->hasOneThrough(
            PeriodoAcademico::class,
            Asignacion::class,
            'id',
            'id',
            'id_asignacion',
            'id_periodo'
        );
    }
}
