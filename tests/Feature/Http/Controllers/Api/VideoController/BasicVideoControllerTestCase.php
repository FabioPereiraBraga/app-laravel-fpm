<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BasicVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
           'opened' => false
        ]);

        $this->sendData = [
            'title' => 'title',
            'description'=> 'description',
            'year_launched'=> 2010,
            'rating'=> Video::RATING_LIST[0],
            'duration'=> 90
        ];
    }

    public function routeStore()
    {
        return  route('videos.store');
    }

    public function routeUpdate()
    {
        return  route('videos.update', ['video' =>  $this->video->id]);
    }
    public function model()
    {
        return Video::class;
    }


}
