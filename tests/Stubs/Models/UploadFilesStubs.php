<?php
namespace Tests\Stubs\Models;

use App\Models\TraitModel\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class UploadFilesStubs extends Model
{
   use UploadFiles;


   protected $table = 'upload_file_stubs';
   protected $fillable = ['name', 'file1', 'file2'];

   public static $filerFilters = ['file1', 'file2','filme','trailer'];

   public static function makeTable()
   {
       \Schema::create('upload_file_stubs', function ($table){
           $table->bigIncrements('id');
           $table->string('name');
           $table->string('file1')->nullable();
           $table->string('file2')->nullable();
           $table->timestamps();
       });
   }

   public static function dropTable()
   {
       \Schema::dropIfExists('upload_file_stubs');
   }

  protected function uploadDir()
  {
      return 1;
  }

  public function relativeFilePath($value)
  {
    return "{$this->uploadDir()}/{$value}";
  }
}
