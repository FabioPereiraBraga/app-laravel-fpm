<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {

    return [
       'name'=>$faker->lastName,
       'type'=> CastMember::TYPES_CAST[ array_rand(CastMember::TYPES_CAST) ]
    ];
});
