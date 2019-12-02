<?php

namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BasicVideoTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'description'=> 'description',
            'duration'=> 90,
            'opened'=>false,
            'rating'=> Video::RATING_LIST[0],
            'title' => 'title',
            'year_launched'=> 2010
        ];
    }

}
