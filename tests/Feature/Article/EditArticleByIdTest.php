<?php
/**
 * EditArticleByIdTest.php
 *
 * Test cases for endpoint Edit Article By Id
 *
 * @author Chesa NH <chesanurhidayat@gmail.com>
 */

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EditArticleByIdTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Article $article;

    protected string $token;

    protected File $file;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and an article
        Article::withoutEvents(function () {
            $this->user = User::factory()
                ->admin()
                ->has(Article::factory()->count(1))
                ->create();
        });
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
        $this->article = $this->user->articles->first();

        // Create a test file for cover
        $this->file = UploadedFile::fake()->image('cover.jpg');
    }

    protected function tearDown(): void
    {
        // Delete created users
        User::where('email', 'like', '%@example.%')->delete();

        parent::tearDown();
    }

    /**
     * Send request to edit article
     *
     * @param  int  $id
     * @param  string  $title
     * @param  string  $content
     * @param  UploadedFile|null  $cover
     * @return TestResponse
     */
    private function send(int $id, string $title, string $content, ?UploadedFile $cover = null): TestResponse
    {
        $data = [
            'title' => $title,
            'content' => $content,
        ];

        if ($cover) {
            $data['cover'] = $cover;
        }

        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])
            ->putJson("/articles/$id", $data);
    }

    /**
     * Test should success
     *
     * @return void
     */
    public function testEditArticleByIdSuccess(): void
    {
        $cover = UploadedFile::fake()->image('cover.jpg');
        Storage::fake('public');

        // Send request to edit article
        $response = $this->send($this->article->id, 'New Title', 'New Content', $cover);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is updated in the database
        $this->assertDatabaseHas('articles', [
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
            'user_id' => $this->user->id,
        ]);

        // Assert that the cover image is stored in storage
        Storage::disk('public')->assertExists("covers/{$cover->hashName()}");

        // Assert that the response contains the expected data
        $response->assertJsonFragment([
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
            'author' => $this->user->name,
        ]);
        /* $response->assertJson(fn (AssertableJson $json) => $json->where('id', $this->article->id)
                ->where('title', 'New Title')
                ->where('content', 'New Content')
                ->where('author', $this->user->name)
                ->has('cover_url')
                ->has('views')
                ->has('created_at')
                ->has('updated_at')
        ); */
    }

    /**
     * Test should return 401 if unauthorized
     *
     * @return void
     */
    public function testEditArticleByIdUnauthorized(): void
    {
        // Send request without a token
        $response = $this->putJson("/articles/{$this->article->id}", [
            'title' => 'New Title',
            'content' => 'New Content',
        ]);

        // Assert that the request returns a 401 status code
        $response->assertUnauthorized();

        // Assert that the article is not updated in the database
        $this->assertDatabaseMissing('articles', [
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
        ]);
    }

    /**
     * Test should return 403 if article is not owned
     *
     * @return void
     */
    public function testEditArticleByIdForbidden(): void
    {
        // Create a new user
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send request with new user's token
        $response = $this->putJson("/articles/{$this->article->id}", [
            'title' => 'New Title',
            'content' => 'New Content',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        // Assert that the request returns a 403 status code
        $response->assertForbidden();

        // Assert that the article is not updated in the database
        $this->assertDatabaseMissing('articles', [
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
        ]);
    }

    /**
     * Test should return 404 if article not found
     *
     * @return void
     */
    public function testEditArticleByIdNotFound(): void
    {
        // Send request to edit non-existing article
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])
            ->putJson('/articles/0', [
                'title' => 'New Title',
                'content' => 'New Content',
                'cover' => $this->file,
            ]);

        // Assert that the request returns a 404 status code
        $response->assertStatus(404);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) => $json->where('message', 'Artikel tidak ditemukan')
        );
    }

    /**
     * Test should return OK if cover is empty
     *
     * @return void
     */
    public function testEditArticleByIdCoverEmpty(): void
    {
        // Send request without cover
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])
            ->putJson("/articles/{$this->article->id}", [
                'title' => 'New Title',
                'content' => 'New Content',
            ]);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is updated in the database
        $this->assertDatabaseHas('articles', [
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
        ]);

        // Assert that the old cover is still in the storage
        Storage::disk('public')->exists('covers/'.$this->article->cover);
    }

    /**
     * Test should remove old image
     *
     * @return void
     */
    public function testEditArticleByIdRemoveOldCover(): void
    {
        // Send request with new cover
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token,
        ])
            ->putJson("/articles/{$this->article->id}", [
                'title' => 'New Title',
                'content' => 'New Content',
                'cover' => $this->file,
            ]);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is updated in the database
        $this->assertDatabaseHas('articles', [
            'id' => $this->article->id,
            'title' => 'New Title',
            'content' => 'New Content',
        ]);

        // Assert that the old cover is removed from storage
        Storage::disk('public')->assertMissing($this->article->cover);
    }
}
