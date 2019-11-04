<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
        $this->castMember->refresh();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));
        $response->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }

    public function testStorage()
    {
        $data = [
            'name'=>'teste',
            'type'=>CastMember::TYPES_CAST[ array_rand(CastMember::TYPES_CAST) ]
        ];

        $this->assertStorage($data, $data + ['deleted_at'=>null]);


    }


    public function testUpdate()
    {
        $typesArray = CastMember::TYPES_CAST;

        $data = [
            'name' => 'test',
            'type'=> current( $typesArray)
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at'=>null] );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        next( $typesArray);
        $type = current( $typesArray);

        $data['type'] = $type;
        $this->assertUpdate($data, array_merge($data,['type'=>$type]) );

    }

    public function testDelete()
    {
       $response = $this->json(
           'DELETE',
           route('cast_members.destroy',['cast_member'=>$this->castMember->id]));
        $response->assertStatus(204);

        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(404);
    }


    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => ''
        ];
        $this->assertInvalidationInStorageAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStorageAction($data, 'max.string',['max' => 255]);



    }

    public function routeStore()
    {
        return  route('cast_members.store');
    }

    public function routeUpdate()
    {
        return  route('cast_members.update', ['cast_member' =>  $this->castMember->id]);
    }
    public function model()
    {
        return CastMember::class;
    }


}
