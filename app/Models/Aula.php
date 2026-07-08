<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aulas';

    protected $fillable = [
        'nombre',
        'edificio',
        'piso',
        'tipo',
        'capacidad_maxima',
        'descripcion',
        'estado',
    ];

    /**
     * Asignaciones (por periodo) hechas a esta aula.
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_aula');
    }
}
