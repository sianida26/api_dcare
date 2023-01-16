<?php
/**
 * DeleteArticleTest.php
 * 
 * Test cases for endpoint Delete Article By Id
 * 
 * @author Chesa NH <chesanurhidayat@gmail.com>
 */

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeleteArticleTest extends TestCase
{
    protected $user = null;
    protected $token = '';
    protected $article = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and an article
        Article::withoutEvents(function(){
            $this->user = User::factory()
                ->admin()
                ->has(Article::factory())
                ->create();
        });
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
        $this->article = $this->user->articles->first();
    }

    protected function tearDown(): void
    {
        //Delete generated users and articles
        User::where('email', 'like', '%@example.%')->delete();
        Article::where('title', 'like', '%Testing title%')->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

     /**
     * Test successful delete article
     * 
     * @return void
     */
    public function testDeleteArticleSuccess(): void
    {
        // Send request to delete article
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->delete("/api/articles/" . $this->article->id);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is deleted in the database
        $this->assertModelMissing($this->article);
    }

     /**
     * Test should return 401 if unauthorized
     * 
     * @return void
     */
    public function testDeleteArticleUnauthorized(): void
    {
        // Send request without token
        $response = $this->delete("/api/articles/" . $this->article->id);

        // Assert that the request returns a 401 status code
        $response->assertStatus(401);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Unauthenticated')
        );
    }

    /**
     * Test should return 403 if article is not owned
     * 
     * @return void
     */
    public function testDeleteNotOwnedArticle(): void
    {
        // Create another user
        $otherUser = User::factory()->create();
        $otherUserToken = $otherUser->createToken('auth_token')->plainTextToken;

        // Send request to delete article not owned by the user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $otherUserToken,
        ])
        ->delete("/api/articles/".$this->article->id);

        // Assert that the request returns a 403 status code
        $response->assertStatus(403);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Anda tidak memiliki akses untuk menghapus artikel ini')
        );
    }

    /**
     * Test should return 404 if article not found
     * 
     * @return void
     */
    public function testDeleteArticleNotFound(): void
    {
        // Send request to delete non-existing article
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])
            ->delete("/api/articles/0");

        // Assert that the request returns a 404 status code
        $response->assertStatus(404);

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Artikel tidak ditemukan')
        );
    }

    /**
     * Test should OK if delete not owned articles if has role "developer"
     * 
     * @return void
     */
    public function testDeleteNotOwnedArticlesAsDeveloper(): void
    {
        // Create a test developer user
        $developer = User::factory()->developer()->create();
        $developerToken = $developer->createToken('auth_token')->plainTextToken;

        // Send request to delete article not owned by the user
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $developerToken,
            ])
            ->delete("/api/articles/" . $this->article->id);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the article is deleted from the database
        $this->assertModelMissing($this->article);
    }
}