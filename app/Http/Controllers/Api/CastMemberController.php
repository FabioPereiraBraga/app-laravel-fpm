<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;

class CastMemberController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name'=>'required|max:255',
            'type'=>'required|in:'. implode(',', CastMember::TYPES_CAST )
        ];
    }

    public function model()
    {
        return CastMember::class;
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
