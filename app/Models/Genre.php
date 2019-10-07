<?php

namespace App\Models;

use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Genre
 *
 * @property string $id
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Genre onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Genre whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Genre withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Genre withoutTrashed()
 * @mixin \Eloquent
 */
class Genre extends Model
{
    use TraitModel, SoftDeletes;

    protected $fillable = ['name','is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id'=>'string',
        'is_active'=>'boolean'
    ];
    public $incrementing = false;
}
