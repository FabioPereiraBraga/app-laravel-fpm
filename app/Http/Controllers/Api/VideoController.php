<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
      $this->rules = [
          'title' => 'required|max:255',
          'description' => 'required',
          'year_launched' => 'required|date_format:Y',
          'opened' => 'boolean',
          'rating' => 'required|in:'.implode(',', Video::RATING_LIST),
          'duration' => 'required|integer',
          'categories_id' => 'required|array|exists:categories,id',
          'genres_id' => 'required|array|exists:genres,id'
      ];
    }

    public function store(Request $request)
    {
        $self = $this;
        $validateData = $this->validate($request,$this->ruleStorage());
           /** @var Video $obj */
        $obj = \DB::transaction(function() use($request, $validateData, $self){
               $obj = $this->model()::create($validateData);
               $self->handleRelations($obj, $request);
               return $obj;
           });

           $obj->refresh();
           return $obj;

    }

    public function update(Request $request, $category)
    {
        $self = $this;
        $dataValidate = $this->validate($request,$this->ruleUpdate());
        $model = $this->findOrFail($category);

        $model = \DB::transaction(function() use($request,$dataValidate,$model, $self){
            $model->update($dataValidate);
            $self->handleRelations($model, $request);
            return $model;
        });

        $model->refresh();
        return  $model;

    }

    protected function handleRelations($video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    }

    protected function model()
    {
       return Video::class;
    }

    protected function ruleStorage()
    {
     return $this->rules;
    }

    protected function ruleUpdate()
    {
        return $this->rules;
    }
}
