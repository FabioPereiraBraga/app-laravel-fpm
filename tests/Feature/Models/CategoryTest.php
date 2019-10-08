<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class,1)->create();
        $attributes = ['id','name','description','created_at','updated_at','deleted_at','is_active'];
        $categories = Category::all();
        $this->assertCount(1, $categories );
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing($attributes ,$categoryKeys);
    }

    public function testCreate()
    {

        $category = Category::create([
            'name'=>'test1'
        ]);

        $category->refresh();
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name'=>'test1',
            'description'=> null
        ]);

        $this->assertNull($category->description);

        $category = Category::create([
            'name'=>'test1',
            'description'=> 'test_description'
        ]);

        $this->assertEquals('test_description', $category->description);

        $category = Category::create([
            'name'=>'test1',
            'is_active'=> false
        ]);

        $this->assertFalse($category->is_active);

        $category = Category::create([
            'name'=>'test1',
            'is_active'=> true
        ]);

        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name'=>'test1',
            'is_active'=> true
        ]);

        $this->assertRegExp('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $category->id);
    }

    public function testDelete()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        $category->delete();
        $this->assertNull(Category::find($category->id));

        $category->restore();
        $this->assertNotNull(Category::find($category->id));
    }

    public function testUpdate()
    {
        $category = Category::create([
            'name'=>'test3',
            'is_active'=> false
        ]);
        $category->is_active = true;
        $category->save();

        $this->assertTrue($category->is_active);
    }

    public function testShow()
    {
        $category = Category::create([
            'name'=>'test3',
            'is_active'=> false
        ]);

        $category = Category::find($category->id);

        $this->assertIsObject($category);
    }
}
