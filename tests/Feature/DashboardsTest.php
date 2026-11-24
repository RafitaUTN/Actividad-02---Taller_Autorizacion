<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboards_require_authentication_and_restrict_roles(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
        $this->getJson('/api/profesor/dashboard')->assertUnauthorized();

        foreach ([
            'admin@academica.test' => [200, 403],
            'profesor@academica.test' => [403, 200],
            'estudiante1@academica.test' => [403, 403],
            'coordinador@academica.test' => [403, 403],
        ] as $email => [$adminStatus, $professorStatus]) {
            $user = User::where('email', $email)->firstOrFail();
            $this->actingAs($user, 'sanctum')->getJson('/api/admin/dashboard')->assertStatus($adminStatus);
            $this->getJson('/api/profesor/dashboard')->assertStatus($professorStatus);
        }
    }
}
