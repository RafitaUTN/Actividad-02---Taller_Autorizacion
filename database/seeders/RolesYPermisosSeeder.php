<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permisos = [
            'estudiantes.ver',
            'estudiantes.crear',
            'estudiantes.editar',
            'estudiantes.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roles = [
            'administrador' => [
                'estudiantes.ver',
                'estudiantes.crear',
                'estudiantes.editar',
                'estudiantes.eliminar',
            ],

            'profesor' => [
                'estudiantes.ver',
                'estudiantes.editar',
            ],

            'coordinador' => [
                'estudiantes.ver',
                'estudiantes.editar',
            ],

            'estudiante' => [
                'estudiantes.ver',
            ],
        ];

        foreach ($roles as $nombre => $permisosRol) {
            $rol = Role::findOrCreate($nombre, 'web');

            $rol->syncPermissions($permisosRol);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
