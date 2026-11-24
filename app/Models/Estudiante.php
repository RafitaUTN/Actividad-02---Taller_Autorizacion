<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'carrera_id',
    'sede_id',
    'cedula',
    'nombre',
    'apellido',
    'correo_institucional',
    'activo',
])]
class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'user_id' => 'integer',
            'carrera_id' => 'integer',
            'sede_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }
}
