<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->video->refresh();


    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['video' => $this->video->id]));
        $response->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testStorage()
    {
        $data = [
            'name'=>'teste'
        ];
        $this->assertStorage($data, $data + ['description'=>null,'is_active'=>true, 'deleted_at'=>null]);

        $data = [
            'name' => 'test',
            'is_active' => false,
            'description' => 'description'
        ];
        $this->assertStorage($data, $data + ['description'=>'description','is_active'=>false]);

    }


    public function testUpdate()
    {


        $data = [ 'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at'=>null] );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);


        $data['description'] = '';
        $this->assertUpdate($data, array_merge($data,['description'=>null]) );

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data,['description'=>null]) );

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data,['description'=>'test']) );



    }

    public function testDelete()
    {
       $response = $this->json(
           'DELETE',
           route('categories.destroy',['video'=>$this->video->id]));
        $response->assertStatus(204);

        $response = $this->get(route('categories.show', ['video' => $this->video->id]));
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
        return  route('categories.update', ['video' =>  $this->video->id]);
    }
    public function model()
    {
        return Video::class;
    }


}
