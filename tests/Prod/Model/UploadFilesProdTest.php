<?php

namespace Tests\Unit\Models;

use Illuminate\Http\UploadedFile;
use Tests\Traits\TestProd;
use Tests\Stubs\Models\UploadFilesStubs;
use Tests\TestCase;


class UploadFilesProdTest extends TestCase
{

    use TestProd;

    private $obj;

    public function setUp(): void
    {
        parent::setUp();
        $this->skipTestIfProd();
        $this->obj = new UploadFilesStubs();
        \Config::set('filesystems.default','gcs');
        $this->deleteAllFiles();     
    }

    public function testUploadfile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        
        $file1 = UploadedFile::fake()->create('video.mp4')->size('10240');
        $file2 = UploadedFile::fake()->create('video2.mp4')->size('500000');
        $this->obj->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        
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
        
        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file1, $file2]);
        $this->obj->deleteFiles([$file1, $file2->hashName()] );

        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");

    }

    public function deleteAllFiles()
    {
        $dirs = \Storage::directories();
        foreach($dirs as $dir){
            $files = \Storage::files($dir);
            \Storage::delete($files);
            \Storage::deleteDirectory($dir);
        }
    }
  


}
