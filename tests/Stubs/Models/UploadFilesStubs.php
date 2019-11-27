<?php
namespace Tests\Stubs\Models;

use Illuminate\Http\UploadedFile;

class UploadFilesStubs
{
   use \App\Models\TraitModel\UploadFiles;

   public static $filerFilters = ['filme', 'banner', 'trailer'];

  protected function uploadDir()
  {
      return 1;
  }
}
