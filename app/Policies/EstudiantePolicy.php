<?php

namespace App\Policies;

use App\Models\Estudiante;
use App\Models\User;

class EstudiantePolicy
{
    public function viewAny(User $user): bool
    {
        if (! $user->can('estudiantes.ver')) {
            return false;
        }

        if ($user->hasRole('administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['profesor', 'coordinador'])) {
            return $user->carrera_id !== null;
        }

        return $user->hasRole('estudiante');
    }

    public function view(User $user, Estudiante $estudiante): bool
    {
        if (! $user->can('estudiantes.ver')) {
            return false;
        }

        if ($user->hasRole('administrador')) {
            return true;
        }

        if ($user->hasAnyRole(['profesor', 'coordinador'])) {
            return $this->perteneceAMismaCarrera($user, $estudiante);
        }

        if ($user->hasRole('estudiante')) {
            return $estudiante->user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('administrador')
            && $user->can('estudiantes.crear');
    }

    public function update(User $user, Estudiante $estudiante): bool
    {
        if (! $user->can('estudiantes.editar')) {
            return false;
        }

        if ($user->hasRole('administrador')) {
            return true;
        }

        if (! $user->hasAnyRole(['profesor', 'coordinador'])) {
            return false;
        }

        return $this->perteneceAMismaCarrera($user, $estudiante)
            && $user->sede_id !== null
            && $estudiante->sede_id !== null
            && $user->sede_id === $estudiante->sede_id;
    }

    public function delete(User $user, Estudiante $estudiante): bool
    {
        return $user->hasRole('administrador')
            && $user->can('estudiantes.eliminar');
    }

    private function perteneceAMismaCarrera(User $user, Estudiante $estudiante): bool
    {
        return $user->carrera_id !== null
            && $estudiante->carrera_id !== null
            && $user->carrera_id === $estudiante->carrera_id;
    }
}
