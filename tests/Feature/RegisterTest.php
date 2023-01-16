<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RegisterTest extends TestCase
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
     * Send data to server
     *
     * @return TestResponse
     */
    private function send(string $name = '', string $email = '', string $password = ''): TestResponse
    {
        return $this->post('/auth/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Test successful registration
     *
     * @return void
     */
    public function testRegisterSuccess(): void
    {
        //Send register request
        $response = $this->send(
            name: 'coba user',
            email: 'testing123@example.com',
            password: '@AxzcaS142'
        );

        //Assert that the request is successful
        $response->assertSuccessful();

        $user = User::firstWhere('email', 'testing123@example.com');

        // Assert that the sent user's data is exists in database
        $this->assertModelExists($user);

        //Assert newly generated user has "user" role
        $this->assertTrue($user->role->name === 'user');

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->where('data.name', $user->name)
                ->where('data.role', $user->role->name)
                ->where('data.email', $user->email)
                ->where('data.profilePicUrl', $user->getProfilePicUrlAttribute())
                ->has('data.accessToken')
        );
    }

    /**
     * Test if email already exists
     *
     * @return void
     */
    public function testEmailAlreadyExists(): void
    {
        $user = User::firstWhere('email', 'testing123@example.com');
        $this->assertModelExists($user);

        //Send register request
        $response = $this->send(
            name: 'coba user',
            email: 'testing123@example.com',
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('email', 'Email ini sudah terdaftar!')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created twice in database
        $this->assertEquals(1, User::where('email', 'testing123@example.com')->count());
    }

    /**
     * Test if name is empty
     *
     * @return void
     */
    public function testNameEmpty(): void
    {
        //Send register request
        $response = $this->send(
            name: '',
            email: 'testnameempty@example.com',
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('name', 'Harus diisi')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => 'testnameempty@example.com']);
    }

    /**
     * Test if email is empty
     *
     * @return void
     */
    public function testEmailEmpty(): void
    {
        //Send register request
        $response = $this->send(
            name: 'ahdflkadfjlkdfhjlasdf',
            email: '',
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('email', 'Harus diisi')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['name' => 'ahdflkadfjlkdfhjlasdf']);
    }

    /**
     * Test if name is empty
     *
     * @return void
     */
    public function testPasswordEmpty(): void
    {
        //Send register request
        $response = $this->send(
            name: fake()->name(),
            email: 'testpasswordempty@example.com',
            password: ''
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('password', 'Harus diisi')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => 'testpasswordempty@example.com']);
    }

    /**
     * Test if name is more that 255 characters
     *
     * @return void
     */
    public function testNameMoreThan255Characters(): void
    {
        //Send register request
        $response = $this->send(
            name: fake()->regexify('\w{256}'),
            email: 'testnamelong@example.com',
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('name', 'Maksimal 255 karakter')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => 'testnamelong@example.com']);
    }

    /**
     * Test if email is not valid
     *
     * @return void
     */
    public function testEmailInvalid(): void
    {
        //Send register request
        $response = $this->send(
            name: fake()->name(),
            email: 'fjsdlfd',
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('email', 'Email tidak sesuai format')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => 'fjsdlfd']);
    }

    /**
     * Test if email is more that 255 characters
     *
     * @return void
     */
    public function testEmailTooLong(): void
    {
        //Generate random long email address
        $longEmail = fake()->regexify('\w{255}@example.com');

        //Send register request
        $response = $this->send(
            name: fake()->name(),
            email: $longEmail,
            password: '@AxzcaS142'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('email', 'Maksimal 255 karakter')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => $longEmail]);
    }

    /**
     * Test if name is less than 8 characters
     *
     * @return void
     */
    public function testPasswordIsTooShort(): void
    {
        $email = fake()->safeEmail();

        //Send register request
        $response = $this->send(
            name: fake()->name(),
            email: $email,
            password: 'abc'
        );

        //Assert that the request is 422
        $response->assertStatus(422);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn ($json) => $json->has('password', 'Password minimal 8 karakter')
                        ->etc()
                )
        );

        // Assert that the sent user data is not created in database
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }
}
