<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStubs;
use Tests\TestCase;

class BasicCrudControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStubs::dropTable();
        CategoryStubs::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStubs::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /**@api @var CategoryStubs $category */
        $category = CategoryStubs::create([
         'name'=>'test_name',
          'description' => 'test description'
        ]);

        $result =  $this->controller->index()->toArray();
        $this->assertEquals([$category->toArray()], $result);
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        //Mockery php
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
                ->once()
                ->andReturn(['name'=>'']);

        $this->controller->store($request);
    }

    public function testStore()
    {
        //Mockery php
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name'=>'test_name', 'description'=>'test_description']);

        $obj = $this->controller->store($request);

        $this->assertEquals(
            CategoryStubs::find(1)->toArray(),
            $obj->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        /**@api @var CategoryStubs $category */
        $category = CategoryStubs::create([
            'name'=>'test_name',
            'description' => 'test description'
        ]);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMelhod = $reflectionClass->getMethod('findOrFail');
        $reflectionMelhod->setAccessible(true);

        $result = $reflectionMelhod->invokeArgs($this->controller,[$category->id]);
        $this->assertInstanceOf(CategoryStubs::class, $result);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {

        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMelhod = $reflectionClass->getMethod('findOrFail');
        $reflectionMelhod->setAccessible(true);

        $result = $reflectionMelhod->invokeArgs($this->controller,[0]);
        $this->assertInstanceOf(CategoryStubs::class, $result);
    }

    public function testShow()
    {
        /**@api @var CategoryStubs $category */
        $category = CategoryStubs::create([
            'name'=>'test_name',
            'description' => 'test description'
        ]);

        $result = $this->controller->show($category->id)->toArray();
        $table = $category->getTable();
        $this->assertDatabaseHas($table,$result);

    }

    public function testUpdate()
    {
        /**@api @var CategoryStubs $category */
        $category = CategoryStubs::create([
            'name'=>'test_name',
            'description' => 'test description'
        ]);

        $paramsUpdate = ['name'=>'test_update', 'description'=>'test_update'];
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn($paramsUpdate);

        $category = $this->controller->update($request, $category->id);
        $this->assertEquals($category->toArray(),CategoryStubs::find($category->id)->toArray());
    }

    public function testDestroy()
    {
        /**@api @var CategoryStubs $category */
        $category = CategoryStubs::create([
            'name'=>'test_name',
            'description' => 'test description'
        ]);

        $response = $this->controller->destroy($category->id);
        $this->createTestResponse($response)
        ->assertStatus(204);
        $this->assertCount(0, CategoryStubs::all());

    }


}
