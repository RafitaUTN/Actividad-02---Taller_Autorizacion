<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionActualizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_null_site_is_rejected_without_changing_the_record(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $record = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;
        $site = $record->sede_id;

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/estudiantes/{$record->id}", ['sede_id' => null]);
        $this->assertSame($site, $record->fresh()->sede_id);
        $response->assertUnprocessable();
    }

    public function test_identity_career_and_site_cannot_be_changed_or_sent_as_null(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $record = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;
        $original = $record->only(['user_id', 'carrera_id', 'sede_id', 'nombre']);

        foreach (['user_id', 'carrera_id', 'sede_id'] as $field) {
            foreach ([null, $original[$field]] as $value) {
                $this->actingAs($admin, 'sanctum')->putJson("/api/estudiantes/{$record->id}", [
                    $field => $value, 'nombre' => 'No debe cambiar',
                ])->assertUnprocessable()->assertJsonValidationErrors([$field]);
                $this->assertSame($original, $record->fresh()->only(array_keys($original)));
            }
        }
    }
}
