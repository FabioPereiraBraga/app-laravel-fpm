<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Lang;
use Tests\Exception\TestException;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    private $genre;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->genre->refresh();

        $this->sendData = [
            'name' => 'name',
            'is_active' => true
        ];

    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);

    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testStorage()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'categories_id'=>[$category->id]
        ]);

        $genre = Genre::find($response->json('id'));

        $response->assertStatus(201)
                 ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'categories_id'=>[$category->id],
            'is_active' => false
        ]);

        $response->assertJsonFragment([
            'is_active' => false
        ]);

    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id'=>'s'
        ];


        $this->assertInvalidationInStorageAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id'=>[100]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $data = [
            'categories_id'=>''
        ];

        $this->assertInvalidationInStorageAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

    }


    public function testRollbackStorage()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('ruleStorage')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);



        try{
            $controller->store($request);
        }catch (TestException $e) {
            $this->assertCount(1, Genre::all());
        }

    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);

        $response = $this->json('PUT',
            route('genres.update', ['genre' => $genre->id]),
            [
                'name' => 'test',
                'is_active' => true,
                'categories_id'=>[$category->id]
            ]);

        $genre = Genre::find($response->json('id'));

        $response->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'is_active' => true
            ]);

    }

    public function testDelete()
    {
       $genre = factory(Genre::class)->create();
       $response = $this->json(
           'DELETE',
           route('genres.destroy',['genre'=>$genre->id]));
        $response->assertStatus(204);

        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $response->assertStatus(404);
    }


    public function testInvalidationData()
    {
        $response = $this->json('POST', route('genres.store'), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertinvalidationMax($response);
        $this->assertinvalidationBoolean($response);

        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT',
            route('genres.update', ['genre' => $genre->id]),
            []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertinvalidationMax($response);
        $this->assertinvalidationBoolean($response);

    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertinvalidationMax(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertinvalidationBoolean(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function routeStore()
    {
        return  route('genres.store');
    }

    public function routeUpdate()
    {
        return  route('genres.update', ['genre' =>  $this->genre->id]);
    }
    public function model()
    {
        return Genre::class;
    }

}
