<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function posted(string $name = '', string $email = '', string $password = ''): TestResponse
    {
        return $this->post(route('register'), [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function test_register_is_successful(): void
    {
        $response = $this->posted(
            name: fake()->name(),
            email: fake()->email(),
            password: '@AxzcaS142'
        );

        $response->assertStatus(201);
    }

    public function test_register_without_name(): void
    {
        $response = $this->posted(
            email: fake()->email(),
            password: fake()->password(8)
        );

        $response->assertStatus(422)
        ->assertJsonPath('errors.name.0', 'The name field is required.');
    }

    public function test_register_with_longer_name(): void
    {
        $response = $this->posted(
            name: Str::random(256),
            email: fake()->email(),
            password: '@AxzcaS142'
        );

        $response->assertStatus(422)
        ->assertJsonPath('errors.name.0', 'The name must not be greater than 255 characters.');
    }

    public function test_register_without_email(): void
    {
        $response = $this->posted(
            name: fake()->name(),
            password: '@AxzcaS142'
        );

        $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email field is required.');
    }

    public function test_register_with_wrong_email_format(): void
    {
        $response = $this->posted(
            name: fake()->name(),
            email: fake()->name(),
            password: '@AxzcaS142'
        );

        $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email must be a valid email address.');
    }

    public function test_register_with_duplicated_email(): void
    {
        $this->test_register_is_successful();

        $user = User::query()->first();

        $response = $this->posted(
            name: fake()->name(),
            email: $user->email,
            password: '@AxzcaS142'
        );

        $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email has already been taken.');
    }

    public function test_register_invalid_password(): void
    {
        $response = $this->posted(
            name: fake()->name(),
            email: fake()->email(),
            password: 'aaaaaa'
        );

        $response->assertStatus(422)
        ->assertJsonFragment([
            'errors' => [
                'password' => [
                    'The password must be at least 8 characters.',
                    'The password must contain at least one uppercase and one lowercase letter.',
                    'The password must contain at least one symbol.',
                    'The password must contain at least one number.',
                ],
            ],
        ]);
    }
}
