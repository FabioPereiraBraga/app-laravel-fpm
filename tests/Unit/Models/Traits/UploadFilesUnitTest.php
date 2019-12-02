<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStubs;
use Tests\TestCase;

class UploadFilesUnitTest extends TestCase
{

    private $obj;

    public function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStubs();
    }

    public function testUploadfile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4')->size('10240');
        $file2 = UploadedFile::fake()->create('video2.mp4')->size('500000');
        $this->obj->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4')->size(1);
        $file2 = UploadedFile::fake()->create('video2.mp4')->size(1);
        $this->obj->uploadFiles([$file1, $file2]);
        $this->obj->deleteOldFiles();
        $this->assertCount(2, \Storage::allFiles());

        $this->obj->oldFiles = [$file1->hashName()];
        $this->obj->deleteOldFiles();
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");

    }

    public function testDeleteFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file->hashName());
        \Storage::assertMissing("1/{$file->hashName()}");

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");

    }

    public function testDeleteFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file1, $file2]);
        $this->obj->deleteFiles([$file1, $file2->hashName()] );

        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");

    }

    public function testExtractFiles()
    {
       $attributes = [];
       $files = $this->obj::extractFiles($attributes);
       $this->assertCount(0, $attributes);
       $this->assertCount(0, $files);

        $attributes = ['filme'=>'test'];
        $files = $this->obj::extractFiles($attributes);
        $this->assertEquals(['filme'=>'test'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['filme'=>'test', 'banner'=>'teste'];
        $files = $this->obj::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['filme'=>'test', 'banner'=>'teste'], $attributes);
        $this->assertCount(0, $files);


        $filme = UploadedFile::fake()->create('video.mp4');
        $attributes = ['filme'=>$filme];
        $files = $this->obj::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['filme'=>$filme->hashName()], $attributes);
        $this->assertEquals([$filme], $files);


        $trailer = UploadedFile::fake()->create('trailer.mp4');
        $attributes = ['filme'=>$filme, 'trailer'=>$trailer];

        $files = $this->obj::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['filme'=>$filme->hashName(),'trailer'=>$trailer->hashName()], $attributes);
        $this->assertEquals([$filme, $trailer], $files);

    }

}
