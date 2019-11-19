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
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->genre->refresh();

        $this->sendData = [
            'name' => 'teste',
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
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testSave()
    {
        $category = factory(Category::class)->create();
        $data = [
            [
                'send_data' => $this->sendData + [
                        'categories_id'=>[$category->id],
                        'is_active'=>true
                    ],
                'test_data' => $this->sendData + [
                        'is_active'=>true
                    ],
            ],
            [
                'send_data' => $this->sendData + [
                        'categories_id'=>[$category->id],
                        'is_active'=>false
                    ],
                'test_data' => $this->sendData + [
                        'is_active'=>false
                    ],
            ],
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStorage(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $this->assertDatabaseHas('category_genre',[
                'genre_id'=>$response->json('id'),
                'category_id'=> $category->id
            ]);
        }

    }

    public function testInvalidationIdCategoriesField()
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

        $category = factory(Category::class)->create();
        $category->delete();

        $data = [
            'categories_id'=>[$category->id]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

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


        $hasError = false;
        try{
            $controller->store($request);
        }catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }


    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name'=>'test'
            ]);

        $controller
            ->shouldReceive('ruleUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);


        $hasError = false;
        try{
            $controller->update($request, 1);
        }catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testDelete()
    {
       $genre = factory(Genre::class)->create();
       $response = $this->json(
           'DELETE',
           route('genres.destroy',['genre'=>$this->genre->id]));
        $response->assertStatus(204);

        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response->assertStatus(404);
    }

    public function testInvalidationNameField()
    {
        $data = [
            'name' => ''
        ];

        $this->assertInvalidationInStorageAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }


    public function testInvalidationMax()
    {
        $data = [
            'name' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStorageAction($data, 'max.string', ['max'=>'255']);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max'=>'255']);
    }

    public function testInvalidationBoolean()
    {
        $data = [
            'is_active' =>'test'
        ];

        $this->assertInvalidationInStorageAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
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
