<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class VideoTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFillableAttribute()
    {
        $video = new Video();
        $fillable =  [
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration'
        ];
        $this->assertEquals($fillable, $video->getFillable()
        );
    }

    public function testIfUserTraits()
    {
        $trait = [
          SoftDeletes::class, TraitModel::class
        ];
        $traitUses = array_values(class_uses(Video::class));

        $this->assertEqualsCanonicalizing($trait, $traitUses);
    }

    public function testCastAttribute()
    {
        $video = new Video();
        $casts = [
            'id'=>'string',
            'opened'=>'boolean',
            'year_launched'=>'integer',
            'duration'=>'integer'
        ];

        $this->assertEquals($casts, $video->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $video = new Video();
        $this->assertFalse($video->incrementing);
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at','created_at','updated_at'];
        $video = (new Video())->getDates();

        sort($dates);
        sort($video);

        $this->assertEquals($dates, $video);
    }

}