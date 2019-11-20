<?php

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = \App\Models\Category::all();
        factory(Genre::class,10)
            ->create()
            ->each(function(Genre $genre) use($categories){
                $categoriesId = $categories->random(5)->pluck('id')->toArray();
                $genre->categories()->attach($categoriesId);
            });
    }
}
