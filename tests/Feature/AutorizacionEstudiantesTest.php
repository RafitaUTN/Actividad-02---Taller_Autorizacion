<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AutorizacionEstudiantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_protected_student_routes_require_authentication(): void
    {
        $this->getJson('/api/estudiantes')->assertUnauthorized();
        $this->getJson('/api/estudiantes/1')->assertUnauthorized();
    }

    public function test_admin_can_list_all_students(): void
    {
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/estudiantes');

        $response->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_professor_and_coordinator_list_only_their_career(): void
    {
        foreach (['profesor@academica.test', 'coordinador@academica.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            $response = $this->actingAs($user, 'sanctum')->getJson('/api/estudiantes');

            $response->assertOk()->assertJsonCount(3, 'data');
            $this->assertSame(
                ['Ana', 'Luis', 'Sofía'],
                collect($response->json('data'))->pluck('nombre')->all(),
            );
        }
    }

    public function test_student_can_list_only_their_own_record(): void
    {
        $student = User::where('email', 'estudiante1@academica.test')->firstOrFail();

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/estudiantes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Ana');
    }

    public function test_student_can_view_own_record_but_not_another_students_record(): void
    {
        $student = User::where('email', 'estudiante1@academica.test')->firstOrFail();
        $own = $student->estudiante;
        $other = User::where('email', 'estudiante2@academica.test')->firstOrFail()->estudiante;

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/estudiantes/{$own->id}")
            ->assertOk();

        $this->getJson("/api/estudiantes/{$other->id}")->assertForbidden();
    }

    public function test_student_cannot_view_another_students_inactive_record(): void
    {
        $student = User::where('email', 'estudiante1@academica.test')->firstOrFail();
        $inactive = User::where('email', 'estudiante5@academica.test')->firstOrFail()->estudiante;

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/estudiantes/{$inactive->id}")
            ->assertForbidden();
    }

    public function test_student_can_view_own_inactive_record(): void
    {
        $student = User::where('email', 'estudiante5@academica.test')->firstOrFail();

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/estudiantes/{$student->estudiante->id}")
            ->assertOk()
            ->assertJsonPath('activo', false);
    }

    public function test_professor_and_coordinator_can_view_same_career_even_from_another_site(): void
    {
        $luis = User::where('email', 'estudiante2@academica.test')->firstOrFail()->estudiante;

        foreach (['profesor@academica.test', 'coordinador@academica.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->actingAs($user, 'sanctum')
                ->getJson("/api/estudiantes/{$luis->id}")
                ->assertOk();
        }
    }

    public function test_professor_and_coordinator_cannot_view_a_different_career(): void
    {
        $maria = User::where('email', 'estudiante3@academica.test')->firstOrFail()->estudiante;

        foreach (['profesor@academica.test', 'coordinador@academica.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $this->actingAs($user, 'sanctum')
                ->getJson("/api/estudiantes/{$maria->id}")
                ->assertForbidden();
        }
    }

    public function test_abac_requires_same_career_and_same_site_for_professor_and_coordinator(): void
    {
        $records = [
            'estudiante1@academica.test' => 200, // same career, same site
            'estudiante2@academica.test' => 403, // same career, different site
            'estudiante3@academica.test' => 403, // different career, same site
            'estudiante4@academica.test' => 403, // different career and site
        ];

        foreach (['profesor@academica.test', 'coordinador@academica.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            foreach ($records as $studentEmail => $expectedStatus) {
                $record = User::where('email', $studentEmail)->firstOrFail()->estudiante;
                $response = $this->actingAs($user, 'sanctum')
                    ->putJson("/api/estudiantes/{$record->id}", ['nombre' => 'Nombre actualizado']);

                $response->assertStatus($expectedStatus);
            }
        }
    }

    public function test_student_cannot_update_even_their_own_record(): void
    {
        $student = User::where('email', 'estudiante1@academica.test')->firstOrFail();
        $record = $student->estudiante;

        $this->actingAs($student, 'sanctum')
            ->putJson("/api/estudiantes/{$record->id}", ['nombre' => 'Intruso'])
            ->assertForbidden();

        $this->assertSame('Ana', $record->fresh()->nombre);
    }

    public function test_admin_can_update_any_student_and_only_admin_can_create_or_delete(): void
    {
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $maria = User::where('email', 'estudiante3@academica.test')->firstOrFail()->estudiante;

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/estudiantes/{$maria->id}", ['nombre' => 'María editada'])
            ->assertOk()
            ->assertJsonPath('nombre', 'María editada');

        $professor = User::where('email', 'profesor@academica.test')->firstOrFail();
        $this->actingAs($professor, 'sanctum')
            ->postJson('/api/estudiantes', [])
            ->assertForbidden();
        $this->deleteJson("/api/estudiantes/{$maria->id}")->assertForbidden();
    }

    public function test_professor_without_matching_site_is_denied_even_if_career_matches(): void
    {
        $professor = User::where('email', 'profesor@academica.test')->firstOrFail();
        $professor->forceFill(['sede_id' => null])->save();
        $ana = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;

        $this->actingAs($professor, 'sanctum')
            ->putJson("/api/estudiantes/{$ana->id}", ['nombre' => 'No autorizado'])
            ->assertForbidden();
    }

    public function test_known_roles_without_required_permissions_are_denied(): void
    {
        $professor = User::where('email', 'profesor@academica.test')->firstOrFail();
        $professor->roles()->where('name', 'profesor')->firstOrFail()
            ->revokePermissionTo('estudiantes.editar');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $record = User::where('email', 'estudiante1@academica.test')->firstOrFail()->estudiante;

        $this->actingAs($professor, 'sanctum')
            ->putJson("/api/estudiantes/{$record->id}", ['nombre' => 'Sin permiso'])
            ->assertForbidden();
    }

    public function test_account_without_role_cannot_list_students(): void
    {
        $unassigned = User::factory()->create();

        $this->actingAs($unassigned, 'sanctum')
            ->getJson('/api/estudiantes')
            ->assertForbidden();
    }

    public function test_admin_can_create_and_delete_a_temporary_student_record(): void
    {
        $admin = User::where('email', 'admin@academica.test')->firstOrFail();
        $career = Carrera::where('nombre', 'Ingeniería en Sistemas')->firstOrFail();
        $site = Sede::where('nombre', 'San Carlos')->firstOrFail();
        $user = User::factory()->create([
            'carrera_id' => $career->id,
            'sede_id' => $site->id,
        ]);
        $user->assignRole('estudiante');

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/estudiantes', [
            'user_id' => $user->id,
            'carrera_id' => $career->id,
            'sede_id' => $site->id,
            'cedula' => 'TEMP-0001',
            'nombre' => 'Temporal',
            'apellido' => 'Prueba',
            'correo_institucional' => 'temporal@academica.test',
        ]);

        $response->assertCreated();
        $id = $response->json('id');

        $this->deleteJson("/api/estudiantes/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Estudiante eliminado correctamente');
        $this->assertDatabaseMissing('estudiantes', ['id' => $id]);
    }
}
