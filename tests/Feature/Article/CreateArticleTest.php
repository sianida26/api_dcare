<?php
/**
 * CreateArticleTest.php
 *
 * Test cases for endpoint Create Article
 *
 * @author Chesa NH <chesanurhidayat@gmail.com>
 */

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

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
        // Remove generated users and articles
        User::where('email', 'like', '%@example.%')->delete();

        parent::tearDown();
    }

    /**
     * Test successful article creation
     *
     * @return void
     */
    public function testCreateArticleSuccess(): void
    {
        // Send request
        $response = $this->post('/articles', [
            'title' => 'Test Article Title',
            'content' => '<p>Test Article Content</p>',
            'cover' => $this->file,
        ], ['Authorization' => 'Bearer '.$this->token]);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is stored in the database
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'content' => '<p>Test Article Content</p>',
            'user_id' => $this->user->id,
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
        $response = $this->post('/articles', [
            'title' => 'Test Article Title',
            'content' => '<p>Test Article Content</p>',
            'cover' => $this->file,
        ], ['Authorization' => 'Bearer '.$token]);

        // Assert that the request is forbidden
        $response->assertForbidden();

        // Assert data should not exists in database
        $this->assertDatabaseMissing('articles', [
            'user_id' => $user->id,
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
        $response = $this->post('/articles', [
            'title' => '',
            'content' => '<p>Test Article Content</p>',
            'cover' => $this->file,
        ], ['Authorization' => 'Bearer '.$this->token]);

        // Assert that the request returns a 422 status code
        $response->assertUnprocessable()
        // Assert that the response contains the expected error message
        ->assertJsonFragment(['message' => 'Periksa kembali data yang dimasukkan'])
        // 'Harus diisi' -- buat locale nya
        ->assertJsonValidationErrorFor('title');
    }

    /**
     * Test content validation
     *
     * @return void
     */
    public function testCreateArticleContentValidation(): void
    {
        // Send request with empty content
        $response = $this->post('/articles', [
            'title' => 'Test Article Title',
            'content' => '',
            'cover' => $this->file,
        ], ['Authorization' => 'Bearer '.$this->token]);

        // Assert that the request returns a 422 status code
        $response->assertUnprocessable()
        // Assert that the response contains the expected error message
        ->assertJsonFragment(['message' => 'Periksa kembali data yang dimasukkan'])
        // 'Harus diisi' -- buat locale nya
        ->assertJsonValidationErrorFor('content');
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
            ->post('/articles', [
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
