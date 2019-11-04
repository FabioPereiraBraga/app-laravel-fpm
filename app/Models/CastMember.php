<?php

namespace App\Models;

use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use TraitModel, SoftDeletes;
    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;
    const TYPES_CAST = [
        self::TYPE_DIRECTOR,
        self::TYPE_ACTOR
    ];

    protected $fillable = ['name','type'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'=>'string',
        'name'=>'string',
        'type'=>'integer'
    ];
    public $incrementing = false;
}
