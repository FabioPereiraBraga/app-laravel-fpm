<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class GenresHasCategoriesRule implements Rule
{
    private $categoriesId;
    private $genreId;

    public function __construct( $categories)
    {
        $categories = (is_array($categories) ) ? $categories : [];
        $this->categoriesId = array_unique($categories);
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
        $categoriesFound = [];
        $value = (is_array($value) ) ? $value : [];
        $this->genreId = array_unique($value);

        if(!count($this->genreId) || !count($this->categoriesId)) {
            return false;
        }

        foreach ($this->genreId as $genreId) {
            $genreCategory = $this->getRows($genreId);
            if(!$genreCategory->count()) {
                return false;
            }
            array_push($categoriesFound, ...$genreCategory->pluck('category_id')->toArray());
        }

        $categoriesFound = array_unique($categoriesFound);
        if(count($categoriesFound) !== count($this->categoriesId)) {
            return false;
        }
        return true;
    }

    protected function getRows($genreId): Collection
    {
        return \DB::table('category_genre')
            ->where('genre_id',$genreId)
            ->whereIn('category_id',  $this->categoriesId)
            ->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.genres__has_categories');
    }
}
