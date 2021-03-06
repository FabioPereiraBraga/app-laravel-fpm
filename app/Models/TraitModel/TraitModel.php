<?php

namespace  App\Models\TraitModel;

use Ramsey\Uuid\Uuid as RamseyUuid;

trait TraitModel {

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::creating(function($obj){
            $obj->id = RamseyUuid::uuid4();
        });
    }
}
