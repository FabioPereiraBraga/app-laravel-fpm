<?php

namespace App\Models;

use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use TraitModel, SoftDeletes;

    protected $fillable = ['name','is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'=>'string'
    ];
}
