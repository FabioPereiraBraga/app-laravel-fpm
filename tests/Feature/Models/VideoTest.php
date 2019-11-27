<?php

namespace Tests\Feature\Models;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\Exception\TestException;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private $data;

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

    public function testRollbackCreate()
    {
        $hasError = false;
        try{
            Video::create($this->data);
        }catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
           $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;

        try{
            Video::create(['title' => 'title',
                'description'=> 'description',
                'year_launched'=> 2010,
                'rating'=> Video::RATING_LIST[0],
                'duration'=> 90,
                'categories_id'=>[0,1,2]
            ], $video->id);
        }catch (QueryException $e) {
            $this->assertDatabaseHas('videos',[
                'title'=>$oldTitle
            ]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testList()
    {
        factory(Video::class,1)->create();
        $attributes = [
             'created_at',
             'deleted_at',
             'description',
             'duration',
             'id',
             'opened',
             'rating',
             'title',
             'updated_at',
             'year_launched'
        ];
        $video = Video::all();
        $this->assertCount(1, $video );
        $videoKeys = array_keys($video->first()->getAttributes());

        $this->assertEqualsCanonicalizing($attributes ,$videoKeys);
    }

    public function testCreateWithBasicField()
    {
        $video = Video::create($this->data);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id) );
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create(array_merge($this->data, ['opened' => true]));
        $video->refresh();

        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create(array_merge($this->data, [
                 'categories_id'=> [$category->id],
                 'genres_id'=> [$genre->id]
            ]) );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);

    }



    public function testUpdateWithBasicField()
    {
        $video = factory(Video::class)->create([
            'opened'=>false
        ]);
        $video->update($this->data);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', array_merge($this->data, ['opened'=> false] ) );


        $video = factory(Video::class)->create([
            'opened'=>false
        ]);
        $video->update(array_merge($this->data,
                ['opened'=>true]
        ));
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened'=> true]);
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create([
            'opened'=>false
        ]);
        $video->update($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);

    }

    public function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video',[
           'video_id'=> $videoId,
           'genre_id'=>$genreId
        ]);
    }

    public function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video',[
            'video_id'=> $videoId,
            'category_id'=>$categoryId
        ]);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
           'categories_id'=>[$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);


        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id'=>[$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);
    }



    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class,3)->create()->pluck('id')->toArray();
        /** @var $video Video */
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'categories_id'=>[$categoriesId[0]]
        ]);
        $this->assertDatabaseHas('category_video',[
          'category_id' => $categoriesId[0],
          'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id'=>[$categoriesId[1], $categoriesId[2]]
        ]);

        $this->assertDatabaseMissing('category_video',[
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video',[
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video',[
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }



    public function testSyncGenres()
    {
        $genresId = factory(Genre::class,3)->create()->pluck('id')->toArray();
        /** @var $video Video */
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'genres_id'=>[$genresId[0]]
        ]);
        $this->assertDatabaseHas('genre_video',[
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'genres_id'=>[$genresId[1], $genresId[2]]
        ]);

        $this->assertDatabaseMissing('genre_video',[
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video',[
            'genre_id' => $genresId[1],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('genre_video',[
            'genre_id' => $genresId[2],
            'video_id' => $video->id
        ]);
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

}
