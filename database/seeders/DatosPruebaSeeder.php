<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        // Sedes
        $sanCarlos = Sede::firstOrCreate([
            'nombre' => 'San Carlos',
        ]);

        $sanJose = Sede::firstOrCreate([
            'nombre' => 'San José',
        ]);

        // Carreras
        $sistemas = Carrera::firstOrCreate([
            'nombre' => 'Ingeniería en Sistemas',
        ]);

        $administracion = Carrera::firstOrCreate([
            'nombre' => 'Administración de Empresas',
        ]);

        // Administrador
        $admin = User::updateOrCreate(
            ['email' => 'admin@academica.test'],
            [
                'name' => 'Administrador',
                'password' => 'Password123!',
                'carrera_id' => null,
                'sede_id' => null,
            ]
        );

        $admin->syncRoles(['administrador']);

        // Profesor
        $profesor = User::updateOrCreate(
            ['email' => 'profesor@academica.test'],
            [
                'name' => 'Profesor de Sistemas',
                'password' => 'Password123!',
                'carrera_id' => $sistemas->id,
                'sede_id' => $sanCarlos->id,
            ]
        );

        $profesor->syncRoles(['profesor']);

        // Coordinador
        $coordinador = User::updateOrCreate(
            ['email' => 'coordinador@academica.test'],
            [
                'name' => 'Coordinador de Sistemas',
                'password' => 'Password123!',
                'carrera_id' => $sistemas->id,
                'sede_id' => $sanCarlos->id,
            ]
        );

        $coordinador->syncRoles(['coordinador']);

        // Misma carrera y misma sede
        $this->crearEstudiante(
            numero: 1,
            nombre: 'Ana',
            apellido: 'Vargas',
            carrera: $sistemas,
            sede: $sanCarlos,
            activo: true
        );

        // Misma carrera y diferente sede
        $this->crearEstudiante(
            numero: 2,
            nombre: 'Luis',
            apellido: 'Mora',
            carrera: $sistemas,
            sede: $sanJose,
            activo: true
        );

        // Diferente carrera y misma sede
        $this->crearEstudiante(
            numero: 3,
            nombre: 'María',
            apellido: 'Rojas',
            carrera: $administracion,
            sede: $sanCarlos,
            activo: true
        );

        // Diferente carrera y diferente sede
        $this->crearEstudiante(
            numero: 4,
            nombre: 'Carlos',
            apellido: 'Castro',
            carrera: $administracion,
            sede: $sanJose,
            activo: true
        );

        // Estudiante inactivo
        $this->crearEstudiante(
            numero: 5,
            nombre: 'Sofía',
            apellido: 'Jiménez',
            carrera: $sistemas,
            sede: $sanCarlos,
            activo: false
        );
    }

    private function crearEstudiante(
        int $numero,
        string $nombre,
        string $apellido,
        Carrera $carrera,
        Sede $sede,
        bool $activo
    ): void {
        $email = "estudiante{$numero}@academica.test";

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => "{$nombre} {$apellido}",
                'password' => 'Password123!',
                'carrera_id' => $carrera->id,
                'sede_id' => $sede->id,
            ]
        );

        $user->syncRoles(['estudiante']);

        Estudiante::updateOrCreate(
            ['user_id' => $user->id],
            [
                'carrera_id' => $carrera->id,
                'sede_id' => $sede->id,
                'cedula' => sprintf('100000%03d', $numero),
                'nombre' => $nombre,
                'apellido' => $apellido,
                'correo_institucional' => $email,
                'activo' => $activo,
            ]
        );
    }
}