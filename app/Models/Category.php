<?php

namespace App\Models;

use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Category extends Model
{
    use TraitModel, SoftDeletes;

    protected $fillable = ['name','description','is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'=>'string'
    ];


}
