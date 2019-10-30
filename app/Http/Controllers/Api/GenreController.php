<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name'=>'required|max:255',
        'is_active'=>'boolean'
    ];

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
