<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
        $this->category->refresh();

    }

    use DatabaseMigrations, TestValidations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    public function testStorage()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $category = Category::find($response->json('id'));

        $response->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'is_active' => false,
            'description' => 'description'
        ]);

        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'description'
        ]);

    }


    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'is_active' => false,
            'description' => 'description'
        ]);

        $response = $this->json('PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]);

        $category = Category::find($response->json('id'));

        $response->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'description' => 'test'
            ]);


        $response = $this->json('PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => ''
            ]);


        $response
            ->assertJsonFragment([
                'description' => null
            ]);

    }

    public function testDelete()
    {
       $response = $this->json(
           'DELETE',
           route('categories.destroy',['category'=>$this->category->id]));
        $response->assertStatus(200);

        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
        $response->assertStatus(404);
    }


    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStorageAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStorageAction($data, 'max.string',['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStorageAction($data, 'boolean');

        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInUpdateAction($data, 'max.string',['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInUpdateAction($data, 'boolean');


    }

    public function routeStore()
    {
        return  route('categories.store');
    }

    public function routeUpdate()
    {
        return  route('categories.update', ['category' =>  $this->category->id]);
    }


}
