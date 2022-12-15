<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected User $user;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create();
    }

    public function test_login_is_successful()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_login_is_unsuccessful()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'Password',
        ]);

        $response->assertStatus(422);
    }
}
