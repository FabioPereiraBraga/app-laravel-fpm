<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\TraitModel\UploadFiles;
use App\Models\Video;
use Arr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
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
             Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes',
            ['values'=> 'video/mp4']
        );
    }

    public function testInvalidationTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
             Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes',
            ['values'=> 'video/mp4']
        );
    }

    public function testInvalidationBannerField()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
             Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidationThumbField()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
             Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
          'POST',
          $this->routeStore(),
          $this->sendData + $files
        );

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files );
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData  + $files
        );
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files );

        $newFiles = [
            'thumb_file' =>  UploadedFile::fake()->create('thumb_file.jpg'),
            'video_file' =>  UploadedFile::fake()->create('video_file.mp4'),
        ];

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData  + $newFiles
        );
        $response->assertStatus(200);
        $this->assertFilesOnPersist(
        $response, 
        Arr::except($files, ['thumb_file', 'video_file']) + $newFiles
        ); 


        $id = $response->json('id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }
    protected function getFiles()
    {
       return [
           'thumb_file' =>  UploadedFile::fake()->create('thumb_file.jpg'),
           'banner_file' =>  UploadedFile::fake()->create('banner_file.jpg'),
           'video_file' =>  UploadedFile::fake()->create('video_file.mp4'),
           'trailer_file' =>  UploadedFile::fake()->create('trailer_file.mp4')
       ];
    }






}
