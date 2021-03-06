<?php

namespace Tests\Feature\Model;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Genre;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class,1)->create();

        $attributes = ['name','is_active','id','created_at','deleted_at','updated_at'];
        $genre = Genre::all();
        $this->assertCount(1, $genre );
        $genreKeys = array_keys($genre->first()->getAttributes());
        $this->assertEqualsCanonicalizing($attributes ,$genreKeys);
    }

    public function testCreate()
    {

        $genre = Genre::create([
            'name'=>'test1'
        ]);

        $genre->refresh();

        $this->assertEquals('test1', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create([
            'name'=>'test1',
            'is_active'=> false
        ]);

        $this->assertEquals('test1', $genre->name);
        $this->assertFalse($genre->is_active);

        $genre = Genre::create([
            'name'=>'test1',
            'is_active'=> false
        ]);

        $this->assertRegExp('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $genre->id);


    }

    public function testDelete()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));

        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }

    public function testUpdate()
    {
        $genre = Genre::create([
            'name'=>'test3',
            'is_active'=> false
        ]);
        $genre->is_active = true;
        $genre->save();

        $this->assertTrue($genre->is_active);
    }

    public function testShow()
    {
        $genre = Genre::create([
            'name'=>'test3',
            'is_active'=> false
        ]);

        $genre = Genre::find($genre->id);

        $this->assertIsObject($genre);
    }
}
