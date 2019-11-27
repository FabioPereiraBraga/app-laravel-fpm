<?php

namespace App\Models;

use App\Models\TraitModel\TraitModel;
use App\Models\TraitModel\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Video extends Model
{
    use TraitModel, SoftDeletes, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration'
    ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'=>'string',
        'opened'=>'boolean',
        'year_launched'=>'integer',
        'duration'=>'integer'
    ];
    public $incrementing = false;
    public static $filerFilters = ['video_file'];


    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try{
            \DB::beginTransaction();
            /** @var Video $obj */
             $obj = static::query()->create($attributes);
             static::handleRelations($obj, $attributes);
             $obj->uploadFiles($files);
             \DB::commit();
            return $obj;
        }catch (\Exception $e) {

            if(isset($obj)) {

            }
            \DB::rollBack();
            throw $e;
        }
    }
    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try{
            \DB::beginTransaction();
             $saved = parent::update($attributes, $options);
             static::handleRelations($this, $attributes);
             if($saved) {
                 $this->uploadFiles($files);
             }
            \DB::commit();
             return $saved;
        }catch (\Exception $e) {

            \DB::rollBack();
            throw $e;
        }
    }

    public static function handleRelations($video,array $atributte)
    {
        if(isset($atributte['categories_id'] )){
            $video->categories()->sync($atributte['categories_id']);
        }
        if(isset($atributte['genres_id'])){
            $video->genres()->sync($atributte['genres_id']);
        }


    }

    protected function uploadDir()
    {
        return $this->id;
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }
}
