<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carreras';

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }
}
