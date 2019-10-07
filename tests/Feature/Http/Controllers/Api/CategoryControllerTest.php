<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
                 ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show',['category'=>$category->id]));

        $response->assertStatus(200)
                 ->assertJson($category->toArray());
    }

    public function testStorage()
    {
        $response = $this->json('POST', route('categories.store'),[
            'name'=>'test'
        ]);

        $category = Category::find($response->json('id'));

        $response->assertStatus(201)
                 ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'),[
            'name'=>'test',
            'is_active'=>false,
            'description'=>'description'
        ]);

        $response->assertJsonFragment([
            'is_active'=>false,
            'description'=>'description'
        ]);

    }



    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'is_active'=>false,
            'description'=>'description'
        ]);

        $response = $this->json('PUT',
            route('categories.update',['category'=>$category->id]),
            [
              'name'=>'test',
              'description'=>'test',
              'is_active'=>true
            ]);

        $category = Category::find($response->json('id'));

        $response->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
            'is_active'=>true,
            'description'=>'test'
            ]);


        $response = $this->json('PUT',
            route('categories.update',['category'=>$category->id]),
            [
                'name'=>'test',
                'description'=>''
            ]);


        $response
            ->assertJsonFragment([
                'description'=>null
            ]);

    }


    public function testInvalidationData()
    {
        $response = $this->json('POST', route('categories.store'), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('categories.store'), [
            'name'=>str_repeat('a',256),
            'is_active'=>'a'
        ]);

        $this->assertinvalidationMax($response);
        $this->assertinvalidationBoolean($response);

        $category = factory(Category::class)->create();
        $response = $this->json('PUT',
            route('categories.update',['category'=>$category->id]),
            []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category'=>$category->id]), [
            'name'=>str_repeat('a',256),
            'is_active'=>'a'
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
                \Lang::get('validation.required',['attribute'=>'name'])
            ]);
    }

    protected function assertinvalidationMax(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string',['attribute'=>'name','max'=> 255])
            ]);
    }

    protected function assertinvalidationBoolean(TestResponse $response)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean',['attribute'=>'is active'])
            ]);
    }

}
