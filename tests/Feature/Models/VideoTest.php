<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Video::class,1)->create();
        $attributes = ['id','name','description','created_at','updated_at','deleted_at','is_active'];
        $video = Video::all();
        $this->assertCount(1, $video );
        $videoKeys = array_keys($video->first()->getAttributes());
        $this->assertEqualsCanonicalizing($attributes ,$videoKeys);
    }

    public function testCreate()
    {

        $video = Video::create([
            'name'=>'test1'
        ]);

        $video->refresh();
        $this->assertEquals('test1', $video->name);
        $this->assertNull($video->description);
        $this->assertTrue($video->is_active);

        $video = Video::create([
            'name'=>'test1',
            'description'=> null
        ]);

        $this->assertNull($video->description);

        $video = Video::create([
            'name'=>'test1',
            'description'=> 'test_description'
        ]);

        $this->assertEquals('test_description', $video->description);

        $video = Video::create([
            'name'=>'test1',
            'is_active'=> false
        ]);

        $this->assertFalse($video->is_active);

        $video = Video::create([
            'name'=>'test1',
            'is_active'=> true
        ]);

        $this->assertTrue($video->is_active);

        $video = Video::create([
            'name'=>'test1',
            'is_active'=> true
        ]);

        $this->assertRegExp('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $video->id);
    }

    public function testDelete()
    {
        /** @var Video $video */
        $video = factory(Video::class)->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }

    public function testUpdate()
    {
        $video = Video::create([
            'name'=>'test3',
            'is_active'=> false
        ]);
        $video->is_active = true;
        $video->save();

        $this->assertTrue($video->is_active);
    }

    public function testShow()
    {
        $video = Video::create([
            'name'=>'test3',
            'is_active'=> false
        ]);

        $video = Video::find($video->id);

        $this->assertIsObject($video);
    }
}
