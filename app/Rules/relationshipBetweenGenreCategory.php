<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\VarDumper\VarDumper;

class relationshipBetweenGenreCategory implements Rule
{
    private $categories;

    public function __construct($categories)
    {
       $this->categories = $categories;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ($this->categories as $category) {
            $genreCategory = \DB::table('category_genre')
                ->where('category_id',$category)
                ->whereIn('genre_id',  $value)
                ->get()->count();

            if($genreCategory === 0) {
                return false;
            }
        }


        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Não encontrado relacionamento entre genre e category';
    }
}
