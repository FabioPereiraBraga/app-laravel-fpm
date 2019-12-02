<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use App\Rules\relationshipBetweenGenreCategory;
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
          'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
          'genres_id' => [
              'required',
              'array',
              'exists:genres,id,deleted_at,NULL'
           ],
          'video_file'=>'mimetypes:video/mp4|max:12' //KB
      ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenreHasCategories($request);
        $validateData = $this->validate($request,$this->ruleStorage());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        return $obj;

    }

    public function update(Request $request, $category)
    {
        $self = $this;
        $this->addRuleIfGenreHasCategories($request);
        $dataValidate = $this->validate($request,$this->ruleUpdate());
        $model = $this->findOrFail($category);
        $model->update($dataValidate);
        $model->refresh();
        return  $model;

    }

    protected function addRuleIfGenreHasCategories(Request $request)
    {
        $categories = ($request->get('categories_id') )? $request->get('categories_id') : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categories);

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
