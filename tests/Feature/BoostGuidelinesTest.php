<?php

use Illuminate\Support\Facades\Blade;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

it('ships Laravel Boost guidelines that render and name real methods', function (): void {
    $guidelines = Blade::render(file_get_contents(__DIR__.'/../../resources/boost/guidelines/core.blade.php'));

    expect($guidelines)
        ->toContain('use HasRelationPages;')
        ->not->toContain('@verbatim');

    preg_match_all('/`(\w+)\(\)?[^`]*`/', $guidelines, $matches);

    $methods = array_filter(
        $matches[1],
        fn (string $method): bool => str_contains($method, 'RelationNavigator') && ! in_array($method, ['withRelationNavigator'], true),
    );

    foreach ($methods as $method) {
        expect(method_exists(HasRelationNavigator::class, $method))->toBeTrue("[{$method}] is not on the trait.");
    }
});
