<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Tests\Stubs\Models\CategoryStubs;

class CategoryControllerStub extends BasicCrudController
{
    private $rules = [
        'name'=>'required|max:255',
        'is_active'=>'boolean'
    ];

    protected function model()
    {
       return CategoryStubs::class;
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
