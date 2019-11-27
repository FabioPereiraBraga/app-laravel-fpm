<?php
namespace  App\Models\TraitModel;

use Illuminate\Http\UploadedFile;

trait UploadFiles
{
    protected abstract function uploadDir();

    /** @param UploadedFile[] $files */
    public function uploadFiles(array $files)
    {
      foreach ($files as $file){
          $this->uploadFile($file);
      }

    }
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file){
            $this->deleteFile($file);
        }
    }

    /**
     * @param $file string|UploadedFile
     */
    public function deleteFile($file)
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
      \Storage::delete("{$this->uploadDir()}/{$filename}");
    }

    public static function extractFiles(array &$attributes = [])
    {
        $files = [];
        foreach (self::$filerFilters as $file) {
            if(isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile){
                $files[] = $attributes[$file];
               $attributes[$file] =  $attributes[$file]->hashName();
            }
        }

        return $files;
    }
}
