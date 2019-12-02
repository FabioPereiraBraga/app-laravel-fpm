<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\VarDumper\VarDumper;
use Tests\Exception\TestException;
use Tests\Feature\Http\Controllers\Api\VideoController\BasicVideoControllerTestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;
use Tests\TestCase;

class VideoControllerUploadsTest extends BasicVideoControllerTestCase
{
    use   TestValidations, TestUploads;

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
             12,
            'mimetypes',
            ['values'=> 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        UploadedFile::fake()->image("image.jpg");
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
          'POST',
          $this->routeStore(),
          $this->sendData + [
              'categories_id' => [$category->id],
              'genres_id' => [$genre->id]
          ] + $files
        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("{$id}/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre    = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ] + $files
        );

        $response->assertStatus(200);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("{$id}/{$file->hashName()}");
        }
    }

    protected function getFiles()
    {
       return [
           'video_file' =>  UploadedFile::fake()->create('video.mp4')
       ];
    }






}
