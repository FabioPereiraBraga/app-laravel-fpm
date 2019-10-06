<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\TraitModel\TraitModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFillableAttribute()
    {
        $categoria = new Category();
        $fillable =  ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $categoria->getFillable()
        );
    }

    public function testIfUserTraits()
    {
        $trait = [
          SoftDeletes::class, TraitModel::class
        ];
        $traitUses = array_values(class_uses(Category::class));

        $this->assertEqualsCanonicalizing($trait, $traitUses);
    }

    public function testCastAttribute()
    {
        $categoria = new Category();
        $casts = [
            'id'=>'string',
            'is_active'
        ];

        $this->assertEquals($casts, $categoria->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $categoria = new Category();
        $this->assertFalse($categoria->incrementing);
    }

    public function testDatesAttribute()
    {
        $dates = ['deleted_at','created_at','updated_at'];
        $categoria = (new Category())->getDates();

        sort($dates);
        sort($categoria);

        $this->assertEquals($dates, $categoria);
    }

}
