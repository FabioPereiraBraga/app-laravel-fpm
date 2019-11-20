<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Symfony\Component\VarDumper\VarDumper;
use Tests\Exception\TestException;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened'=>false
        ]);
        $this->video->refresh();

        $this->sendData = [
            'title' => 'title',
            'description'=> 'description',
            'year_launched'=> 2010,
            'rating'=> Video::RATING_LIST[0],
            'duration'=> 90
        ];


    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));
        $response->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description'=> '',
            'year_launched'=> '',
            'rating'=> '',
            'duration'=> '',
            'categories_id' => '',
            'genres_id'=>''
        ];

        $this->assertInvalidationInStorageAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function  testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256)
        ];

        $this->assertInvalidationInStorageAction($data, 'max.string', ['max'=>'255']);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max'=>'255']);
    }

    public function testInvalidationInteger()
    {
        $data = [
          'duration'=>'s'
        ];

        $this->assertInvalidationInStorageAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
          'year_launched'=>'a'
        ];

        $this->assertInvalidationInStorageAction($data, 'date_format', ['format'=>'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format'=>'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened'=>'s'
        ];

        $this->assertInvalidationInStorageAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id'=>'s'
        ];


        $this->assertInvalidationInStorageAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id'=>[100]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');


        $category = factory(Category::class)->create();
        $category->delete();

        $data = [
            'categories_id'=>[$category->id]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');


    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genres_id'=>'s'
        ];


        $this->assertInvalidationInStorageAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id'=>[100]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();

        $data = [
            'genres_id'=>[$genre->id]
        ];

        $this->assertInvalidationInStorageAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating'=> 0
        ];

        $this->assertInvalidationInStorageAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testRollbackStorage()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('ruleStorage')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);



        try{
            $controller->store($request);
        }catch (TestException $e) {
            $this->assertCount(1, Video::all());
        }

    }
    public function testSave()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $data = [
            [
              'send_data' => $this->sendData + [
                  'categories_id'=>[$category->id],
                  'genres_id'=>[$genre->id]
              ],
              'test_data' => $this->sendData + ['opened'=> false]
            ],
            [
                'send_data' => $this->sendData + [
                   'opened'=> true,
                   'categories_id'=>[$category->id],
                   'genres_id'=>[$genre->id]
                ],
                'test_data' => $this->sendData + ['opened'=> true]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[1],
                    'categories_id'=>[$category->id],
                    'genres_id'=>[$genre->id]
                 ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ]
        ];

        foreach ($data as $key => $value){
            $response = $this->assertStorage(
                $value['send_data'],
                $value['test_data'] + ['deleted_at'=>null]
            );
            $response->assertJsonStructure([
               'created_at',
               'updated_at'
            ]);

            $this->assertHasCategory($response->json('id'), $category->id);
            $this->assertHasGenre($response->json('id'), $genre->id);

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at'=>null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory($response->json('id'), $category->id);
            $this->assertHasGenre($response->json('id'), $genre->id);
        }
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class,3)->create()->pluck('id')->toArray();
        /** @var $genre Genre */
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;


        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +[
                'genres_id'=>[$genreId],
                'categories_id'=>[$categoriesId[0]]
            ]);

        $this->assertDatabaseHas('category_video',[
            'category_id'=>$categoriesId[0],
            'video_id'=>$response->json('id')
        ]);

        $response = $this->json(
            'PUT',
            route('videos.update',['video'=>$response->json('id')]),
            $this->sendData +[
                'genres_id'=>[$genreId],
                'categories_id'=>[$categoriesId[1], $categoriesId[2]]
            ]
        );
        $this->assertDatabaseMissing('category_video',[
            'category_id'=> $categoriesId[0],
            'video_id'=>$response->json('id')

        ]);
        $this->assertDatabaseHas('category_video',[
            'category_id'=>$categoriesId[1],
            'video_id'=>$response->json('id')
        ]);

        $this->assertDatabaseHas('category_video',[
            'category_id'=>$categoriesId[2],
            'video_id'=>$response->json('id')
        ]);
    }




    public function testSyncGenres()
    {
        /** @var Collection $genres */
        $genres  = factory(Genre::class,3)->create();
        $geresId = $genres->pluck('id')->toArray();
        $categoriesId = factory(Category::class)->create()->id;
        $genres->each(function($genre) use ($categoriesId)  {
           $genre->categories()->sync($categoriesId);
        });



        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +[
                'genres_id'=>[$geresId[0]],
                'categories_id'=>[$categoriesId]
            ]);

        $this->assertDatabaseHas('genre_video',[
            'genre_id'=>$geresId[0],
            'video_id'=>$response->json('id')
        ]);

        $response = $this->json(
            'PUT',
            route('videos.update',['video'=>$response->json('id')]),
            $this->sendData +[
                'genres_id'=>[$geresId[1], $geresId[2]],
                'categories_id'=>[$categoriesId]
            ]
        );
        $this->assertDatabaseMissing('genre_video',[
            'genre_id'=> $geresId[0],
            'video_id'=>$response->json('id')

        ]);
        $this->assertDatabaseHas('genre_video',[
            'genre_id'=>$geresId[1],
            'video_id'=>$response->json('id')
        ]);

        $this->assertDatabaseHas('genre_video',[
            'genre_id'=>$geresId[2],
            'video_id'=>$response->json('id')
        ]);
    }


    protected function assertHasCategory($videoId, $categoriesId)
    {
        $this->assertDatabaseHas('category_video',[
            'video_id'=>$videoId,
            'category_id'=>$categoriesId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video',[
            'video_id'=>$videoId,
            'genre_id'=>$genreId
        ]);
    }

    public function testDelete()
    {
       $response = $this->json(
           'DELETE',
           route('videos.destroy',['video'=>$this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
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
