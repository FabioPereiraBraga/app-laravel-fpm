<?php

namespace App\Http\Controllers\Api;

use App\Models\VideoApp\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {

    }

    protected function model()
    {
       return Video::class;
    }

    protected function ruleStorage()
    {

    }

    protected function ruleUpdate()
    {
        // TODO: Implement ruleUpdate() method.
    }
}
