<?php
/**
 * GetArticleByIdTest.php
 * 
 * Test cases for endpoint Get Article By ID
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

class GetArticleByIdTest extends TestCase
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

        // Create a test user with a generated article
        Article::withoutEvents(function(){
            $this->user = User::factory()
                ->admin()
                ->has(Article::factory()->count(1))
                ->create();
        });
            
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
        $this->article = $this->user->articles->first();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        //Delete generated users
        User::where('email', 'like', '%@example.%')->delete();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * Test success get article by id
     * 
     * @return void
     */
    public function testGetArticleByIdSuccess(): void
    {
        // Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles/'.$this->article->id);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $this->article->id)
                ->where('title', $this->article->title)
                ->where('cover_url', $this->article->cover_url)
                ->where('content', $this->article->content)
                ->where('author', $this->user->name)
                ->where('views', $this->article->views)
                ->where('created_at', $this->article->created_at)
                ->where('updated_at', $this->article->updated_at)
        );
    }

    /**
     * Test should 404 if the article is not owned
     * 
     * @return void
     */
    public function testGetArticleByIdNotOwned(): void
    {
        $otherUser = User::factory()->create();
        $otherUserArticle = Article::factory()->create([
            'author_id' => $otherUser->id
        ]);

        // Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])->get('/api/articles/'.$otherUserArticle->id);

        // Assert that the request returns a 404 status code
        $response->assertStatus(404);

        // Assert that the response contains the expected message
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('message', 'Artikel tidak ditemukan')
        );
    }

    /**
     * Test should success if has developer role
     * 
     * @return void
     */
    public function testGetArticleByIdSuccessWithDeveloperRole(): void
    {
        
        $this->user->assignRole('developer');
        $this->user->save();

        // Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles/'.$this->article->id);

        // Assert that the request is successful
        $response->assertSuccessful();

        // Assert that the response contains the expected data
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $this->article->id)
                ->where('title', $this->article->title)
                ->where('cover_url', $this->article->cover_url)
                ->where('content', $this->article->content)
                ->where('author', $this->user->name)
                ->where('views', $this->article->views)
                ->where('created_at', $this->article->created_at)
                ->where('updated_at', $this->article->updated_at)
        );
    }

    /**
     * Test should 401 if not logged in
     * 
     * @return void
     */
    public function testGetArticleByIdUnauthorized(): void
    {
        // Send request without auth token
        $response = $this->get('/api/articles/'.$this->article->id);

        // Assert that the request returns a 401 status code
        $response->assertStatus(401);

        // Assert that the response contains the expected message
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Unauthenticated')
        );
    }

    /**
     * Test should 404 if the article is not found
     * 
     * @return void
     */
    public function testGetArticleByIdNotFound(): void
    {
        // Send request for non-existing article
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles/999999');

        // Assert that the request returns a 404 status code
        $response->assertStatus(404);

        // Assert that the response contains the expected message
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message', 'Artikel tidak ditemukan')
        );
    }
}
