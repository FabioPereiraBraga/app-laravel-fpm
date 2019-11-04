<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(CastMember::class,1)->create();
        $attributes = ['id','name','type','created_at','updated_at','deleted_at'];
        $castMembers = CastMember::all();
        $this->assertCount(1, $castMembers );
        $castMemberKeys = array_keys($castMembers->first()->getAttributes());
        $this->assertEqualsCanonicalizing($attributes ,$castMemberKeys);
    }

    public function testCreate()
    {
        $castMember = CastMember::create([
            'name'=>'test1',
            'type'=> CastMember::TYPES_CAST[ array_rand(CastMember::TYPES_CAST) ]
        ]);

        $castMember->refresh();
        $this->assertEquals('test1', $castMember->name);
        $type = CastMember::TYPES_CAST[ array_rand(CastMember::TYPES_CAST) ];

        $castMember = CastMember::create([
            'name'=>'test1',
            'type'=> $type
        ]);

        $this->assertEquals($type, $castMember->type);
        $this->assertRegExp('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $castMember->id);
    }

    public function testDelete()
    {
        /** @var CastMember $castMember */
        $castMember = factory(CastMember::class)->create();
        $castMember->delete();
        $this->assertNull(CastMember::find($castMember->id));

        $castMember->restore();
        $this->assertNotNull(CastMember::find($castMember->id));
    }

    public function testUpdate()
    {
        $typesArray = CastMember::TYPES_CAST;
        $castMember = CastMember::create([
            'name'=>'test3',
            'type'=> current( $typesArray)
        ]);
        next( $typesArray);
        $type = current( $typesArray);
        $castMember->type = $type;
        $castMember->save();

        $this->assertEquals($type, $castMember->type);
    }

    public function testShow()
    {
        $castMember = CastMember::create([
            'name'=>'test3',
            'type'=>  CastMember::TYPES_CAST[ array_rand(CastMember::TYPES_CAST) ]
        ]);

        $castMember = CastMember::find($castMember->id);

        $this->assertIsObject($castMember);
    }
}
