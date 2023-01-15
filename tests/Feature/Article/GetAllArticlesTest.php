<?php
/**
 * GetAllArticlesTest.php
 * 
 * Test cases for endpoint Get All Articles
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
use Tests\TestCase;

class GetAllArticlesTest extends TestCase
{

    protected $user = null;
    protected $token = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }
    

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user with random 5 generated articles
        Article::withoutEvents(function(){
            $this->user = User::factory()
                ->admin()
                ->has(Article::factory()->count(5))
                ->create();
        });
            
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
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
     * Test successful
     * 
     * @return void
     */
    public function testGetAllArticlesSuccess(): void
    {
        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/articles');

        //Assert that the request is successful
        $response->assertSuccessful();
    }

    /**
     * Test successful get all user's generated articles
     * 
     * @return void
     */
    public function testShouldContains_5Articles(): void
    {
        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains 5 articles
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 5)
                ->has('data.0', fn ($json) =>
                    $json->has('id')
                        ->has('title')
                        ->has('cover_url')
                        ->has('content')
                        ->where('author', $this->user->name)
                        ->has('views')
                        ->has('created_at')
                        ->has('updated_at')
                )
                ->etc()
        );
    }

    /**
     * Test the response should contain currentPage
     * 
     * @return void
     */
    public function testShouldContainsCurrentPage(): void
    {
        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles?perPage=2&page=3');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains currentPage property
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('currentPage', 3)
                ->etc()
        );
    }

    /**
     * Test the response should contains per_page property
     * 
     * @return void
     */
    public function testShouldContainsPerPage(): void
    {
        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles?perPage=2&page=3');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains currentPage property
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('perPage', 2)
                ->etc()
        );
    }

    /**
     * Test the response should contains total amount of articles
     * 
     * @return void
     */
    public function testShouldContainsTotal(): void
    {
        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles?perPage=2&page=3');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains currentPage property
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('total', 5)
                ->etc()
        );
    }

    /**
     * Test should not access this endpoint if unauthenticated
     * 
     * @return void
     */
    public function testShouldUnauthenticatedIfNotLoggedIn(): void
    {
        //Send request
        $response = $this->get('/api/articles?perPage=2&page=3');

        //Assert that the request is unauthorized (401)
        $response->assertUnauthorized();

        //Assert should contains message only
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('message', 'Unauthenticated')
        );
    }

    /**
     * Test should only show user's generated articles only
     * 
     * @return void
     */
    public function testShouldShowOwnArticlesOnly(): void
    {

        //Generate 10 random articles by other user
        Article::withoutEvents(function(){
            Article::factory()->count(10)->create();
        });

        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
            ->get('/api/articles');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains 5 articles only
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 5)
                ->etc()
        );
    }

    /**
     * Test should show all articles if has developer role
     * 
     * @return void
     */
    public function testShouldShowAllArticlesIfDeveloper(): void
    {

        //Generate 10 random articles by other user
        Article::withoutEvents(function(){
            Article::factory()->count(10)->create();
        });

        $developer = User::factory()->developer()->create();
        $token = $developer->create_token('auth_token')->plainTextToken;

        //Send request
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $token])
            ->get('/api/articles');

        //Assert that the request is successful
        $response->assertSuccessful();

        //Assert should contains 5 articles only
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 15)
                ->etc()
        );
    }
}
