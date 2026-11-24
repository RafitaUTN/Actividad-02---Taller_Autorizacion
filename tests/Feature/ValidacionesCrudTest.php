<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_records_with_invalid_or_duplicate_fields(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $existing = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;
        $before = \App\Models\Estudiante::count();
        $this->actingAs($admin, 'sanctum')->postJson('/api/estudiantes', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'carrera_id', 'sede_id', 'cedula', 'nombre', 'apellido', 'correo_institucional']);
        $this->postJson('/api/estudiantes', [
            'user_id' => $existing->user_id,
            'carrera_id' => 999999,
            'sede_id' => 999999,
            'cedula' => $existing->cedula,
            'nombre' => 'Prueba',
            'apellido' => 'Prueba',
            'correo_institucional' => $existing->correo_institucional,
        ])->assertUnprocessable()->assertJsonValidationErrors(['user_id', 'carrera_id', 'sede_id', 'cedula', 'correo_institucional']);
        $this->assertSame($before, \App\Models\Estudiante::count());
    }

    public function test_duplicate_update_is_rejected_without_modifying_the_record(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $first = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;
        $second = User::where('email', 'estudiante2@academica.test')->firstOrFail()->estudiante;
        $original = $first->only(['cedula', 'correo_institucional', 'nombre']);

        $this->actingAs($admin, 'sanctum')->putJson("/api/estudiantes/{$first->id}", [
            'cedula' => $second->cedula,
            'correo_institucional' => $second->correo_institucional,
            'nombre' => 'No debe guardarse',
        ])->assertUnprocessable()->assertJsonValidationErrors(['cedula', 'correo_institucional']);
        $this->assertSame($original, $first->fresh()->only(array_keys($original)));
    }
}
