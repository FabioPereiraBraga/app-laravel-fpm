<?php

namespace Tests\Unit\Models;

use App\Rules\GenresHasCategoriesRule;
use Mockery\MockInterface;
use Tests\TestCase;

class GenresHasCategoriesRuleTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new \App\Rules\GenresHasCategoriesRule([1,1,2,2]);
        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $categoriesId);
    }
    public function testGenresIdValue()
    {
        $rule = $this->createRuleMock([]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturnNull();

        $rule->passes('', [1,1,2,2]);

        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('genreId');
        $reflectionProperty->setAccessible(true);

        $genreId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $genreId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1] ) );
    }

    public function testPassesReturnsFalseWhenGetRownsIsEmpty()
    {
       $rules = $this->createRuleMock([1]);
       $rules
           ->shouldReceive('getRows')
           ->withAnyArgs()
           ->andReturn(collect());
       $this->assertFalse($rules->passes('', [1]));
    }

    public function testPassesreturnFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect(['category_id' => 1] ) );

        $this->assertFalse($rule->passes('',[1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2]
            ]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenresHasCategoriesRule::class, [$categoriesId])
               ->makePartial()
               ->shouldAllowMockingProtectedMethods();
    }
}
