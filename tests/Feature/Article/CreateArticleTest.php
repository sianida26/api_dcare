<?php
/**
 * CreateArticleTest.php
 * 
 * Test cases for endpoint Create Article
 * 
 * @author Chesa NH <chesanurhidayat@gmail.com>
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    protected $user = null;
    protected $token = '';
    protected $file = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user with admin role
        $this->user = User::factory()->admin()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;

        // Create a test file for cover
        $this->file = UploadedFile::fake()->image('cover.jpg');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Remove generated users and articles
        User::where('email', 'like', '%@example.%')->delete();
    }

    /**
     * Test successful article creation
     * 
     * @return void
     */
    public function testCreateArticleSuccess(): void
    {
        // Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->post('/api/articles', [
                'title' => 'Test Article Title',
                'content' => '<p>Test Article Content</p>',
                'cover' => $this->file
            ]);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is stored in the database
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'content' => '<p>Test Article Content</p>',
            'user_id' => $this->user->id
        ]);

        // Assert that the cover image is stored in the storage
        Storage::disk('public')->assertExists('covers/'.$this->file->hashName());
    }

    /**
     * Test forbidden if not admin or developer role
     * 
     * @return void
     */
    public function testCreateArticleForbidden(): void
    {
        // Create a test user with user role
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $token])
            ->post('/api/articles', [
                'title' => 'Test Article Title',
                'content' => '<p>Test Article Content</p>',
                'cover' => $this->file
            ]);

        // Assert that the request is forbidden
        $response->assertForbidden();
        
        // Assert data should not exists in database
        $this->assertDatabaseMissing('articles', [
            'user_id' => $user->id
        ]);
    }

    /**
     * Test title validation
     * 
     * @return void
     */
    public function testCreateArticleTitleValidation(): void
    {
        // Send request with empty title
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->post('/api/articles', [
                'title' => '',
                'content' => '<p>Test Article Content</p>',
                'cover' => $this->file
            ]);

        // Assert that the request returns a 422 status code
        $response->assertUnprocessable();

        // Assert that the response contains the expected error message
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn($json) => 
                    $json->has('title', 'Harus diisi')
                        ->etc()
                )
        );
    }

    /**
     * Test content validation
     * 
     * @return void
     */
    public function testCreateArticleContentValidation(): void
    {
        // Send request with empty content
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->post('/api/articles', [
                'title' => 'Test Article Title',
                'content' => '',
                'cover' => $this->file
            ]);

        // Assert that the request returns a 422 status code
        $response->assertStatus(422);

        // Assert that the response contains the expected error message
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Periksa kembali data yang dimasukkan')
                ->has('errors', fn($json) => 
                    $json->has('content', 'Harus diisi')
                        ->etc()
                )
        );
    }

    /**
     * Test cover validation
     * 
     * @return void
     */
    /* public function testCreateArticleCoverValidation(): void
    {
        // Send request without cover
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->post('/api/articles', [
                'title' => 'Test Article Title',
                'content' => '<p>Test Article Content</p>',
            ]);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is stored in the database without cover
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'content' => '<p>Test Article Content</p>',
            'author_id' => $this->user->id,
            'cover_url' => null, //TODO: Returns default cover_url
        ]);
    } */
}
