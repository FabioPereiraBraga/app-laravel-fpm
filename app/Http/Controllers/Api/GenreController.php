<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name'=>'required|max:255',
        'is_active'=>'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];


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

    protected function handleRelations($model, Request $request)
    {
        $model->categories()->sync($request->get('categories_id'));
    }

    public function model()
    {
        return Genre::class;
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
