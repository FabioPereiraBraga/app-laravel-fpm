<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BasicCrudController
{
    private $rules = [
        'name'=>'required|max:255',
        'description'=>'nullable',
        'is_active'=>'boolean'
    ];

    public function model()
    {
        return Category::class;
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
