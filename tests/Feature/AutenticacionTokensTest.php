<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_issues_a_token_without_granting_privileges(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Cuenta de prueba',
            'email' => 'registro@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertCreated()->assertJsonStructure(['user' => ['id'], 'token']);

        $user = User::findOrFail($response->json('user.id'));
        $this->assertCount(0, $user->getRoleNames());
        $this->assertCount(0, $user->getAllPermissions());
        $this->withToken($response->json('token'))->getJson('/api/me')
            ->assertOk()->assertJsonPath('user.id', $user->id);
    }

    public function test_register_validates_fields_and_rejects_privilege_assignment(): void
    {
        $this->postJson('/api/register', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
        $this->postJson('/api/register', [
            'name' => str_repeat('a', 121),
            'email' => 'no-es-correo',
            'password' => 'corta',
            'password_confirmation' => 'diferente',
            'role' => 'administrador',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
        $this->assertDatabaseCount('users', 0);

        User::factory()->create(['email' => 'existente@example.test']);
        $this->postJson('/api/register', [
            'name' => 'Prueba', 'email' => 'existente@example.test',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_me_and_logout_use_a_real_revocable_sanctum_token(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Password123!'])
            ->assertOk()->assertJsonStructure(['token'])->json('token');
        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        // Cada solicitud HTTP real resuelve nuevamente los guards.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }
}
