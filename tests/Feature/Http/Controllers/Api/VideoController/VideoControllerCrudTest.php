<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Symfony\Component\VarDumper\VarDumper;
use Tests\Exception\TestException;
use Tests\Feature\Http\Controllers\Api\VideoController\BasicVideoControllerTestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class VideoControllerCrudTest extends BasicVideoControllerTestCase
{
    use TestValidations, TestSaves;



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

    public function testSaveWithoutFiles()
    {
        
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);

        $data = [
            [
                'send_data'=> $this->sendData,
                'test_data'=>$testData + ['opened'=> false]
            ],
            [
                'send_data'=> $this->sendData + [
                        'opened' => true
                    ],
                'test_data'=>$testData + ['opened'=> true]
            ],
            [
                'send_data'=> $this->sendData + [
                        'rating' => Video::RATING_LIST[1]
                    ],
                'test_data'=>$testData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStorage(
              $value['send_data'],
              $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );
            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );

            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );
        }

    }


    public function testInvalidationRatingField()
    {
        $data = [
            'rating'=> 0
        ];

        $this->assertInvalidationInStorageAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testSave()
    {
        
        
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);
        $data = [
            [
              'send_data' => $testData + [
                  'categories_id'=>[$category->id],
                  'genres_id'=>[$genre->id]
              ],
              'test_data' => $testData + ['opened'=> false]
            ],
            [
                'send_data' => $testData + [
                   'opened'=> true,
                   'categories_id'=>[$category->id],
                   'genres_id'=>[$genre->id]
                ],
                'test_data' => $testData + ['opened'=> true]
            ],
            [
                'send_data' => $testData + [
                    'rating' => Video::RATING_LIST[1],
                    'categories_id'=>[$category->id],
                    'genres_id'=>[$genre->id]
                 ],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
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



}
