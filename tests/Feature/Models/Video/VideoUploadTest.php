<?php

namespace Tests\Feature\Models\Video;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\Exception\TestException;
use Tests\TestCase;

class VideoUploadTest extends BasicVideoTestCase
{

    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4'),
            ]
        );

        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

    }

    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function() {
            throw new TestException();
        });
        $hasError = false;

        try {
            $video = Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4'),
                ]
            );
        }catch (TestException $e) {
             $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);

    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        $thumbfile = UploadedFile::fake()->image('thumb.jpg');
        $videofile = UploadedFile::fake()->create('video.mp4');
        $video->update($this->data + [
           'thumb_file'=>$thumbfile,
           'video_file'=>$videofile
        ]);

        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->image('video.png');
        $video->update($this->data + [
           'video_file' => $newVideoFile
        ]);

        \Storage::assertExists("{$video->id}/{$thumbfile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$videofile->hashName()}");
    }

    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function() {
            throw new TestException();
        });
        $hasError = false;

        try {
            $video->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4'),
                ]
            );
        }catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);

    }

    public function testFileUrlWithLocalDriver()
    {
        $fileFields = [];
        foreach(Video::$filerFilters as $field) {
          $fileFields[$field] = "$field.test";
        }
        $video = factory(Video::class)->create($fileFields);
        $localDriver = config('filesystems.default');
        $baseUrl = config("filesystems.disks.{$localDriver}.url");
        foreach($fileFields as $field => $value){
           $fileUrl = $video->{"{$field}_url"};
           $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlWithGcsDriver()
    {
        $fileFields = [];
        foreach(Video::$filerFilters as $field) {
          $fileFields[$field] = "$field.test";
        }
        $video = factory(Video::class)->create($fileFields);
        $baseUrl = config('filesystems.disks.gcs.storage_api_uri');
        \Config::set('filesystems.default','gcs');
        foreach($fileFields as $field => $value){
           $fileUrl = $video->{"{$field}_url"};
           $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlsIfNullWhereFieldsAreNull()
    {
        $video = factory(Video::class)->create();
        foreach(Video::$filerFilters as $field) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertNull($fileUrl);
          }
    }
}
