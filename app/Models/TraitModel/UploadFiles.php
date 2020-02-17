<?php
namespace  App\Models\TraitModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

trait UploadFiles
{
    protected abstract function uploadDir();
    public $oldFiles = [];

    public static function bootUploadFiles()
    {
     static::updating(function(Model $model){
         $fieldsUpdated = array_keys($model->getDirty());
         $fileUpdated = array_intersect($fieldsUpdated, self::$filerFilters);
         $filesFilter = Arr::where($fileUpdated, function($fileField) use($model) {
             return $model->getOriginal($fileField); // !== null
         });
         $model->oldFiles = array_map(function($fileField) use($model){
             return $model->getOriginal($fileField); // pega o valor original (antigo);
         }, $filesFilter);
     });
    }
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

    public function deleteOldFiles()
    {
      $this->deleteFiles($this->oldFiles);
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
