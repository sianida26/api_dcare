<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function tearDownAfterClass(): void
    {
        (new self())->setUp();

        //remove generated users
        User::where('email', 'like', '%@example.%')->delete();

        parent::tearDownAfterClass();
    }

    /**
     * Test login with correct credentials
     *
     * @return void
     */
    public function testLoginSuccess()
    {
        // Create a test user
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        // Prepare the login data
        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        // Send a login request
        $response = $this->post('/auth/login', $data);

        // Assert that the request was successful
        $response->assertStatus(200);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->where('name', $user->name)
                ->where('role', $user->role->name)
                ->where('email', $user->email)
                ->where('profilePicUrl', $user->getProfilePicUrlAttribute())
                ->has('accessToken')
        );
    }

    /**
     * Test login with incorrect credentials
     *
     * @return void
     */
    public function testLoginFailed()
    {
        // Create a test user
        $user = User::factory()->create([
            'password' => bcrypt('someWrongPassword'),
        ]);

        // Prepare the login data
        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        // Send a login request
        $response = $this->post('/auth/login', $data);

        // Assert that the request was successful
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', 'Username atau password salah!')
        );

        // Assert that the user is not logged in
        $this->assertGuest();
    }
}
