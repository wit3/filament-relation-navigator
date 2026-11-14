<?php

/**
 * The documentation site includes README sections by region: every
 * translated README must have the same, balanced regions as the English one.
 */
function readmeRegions(string $file): array
{
    $content = file_get_contents(__DIR__.'/../../'.$file);

    preg_match_all('/<!-- #region ([a-z0-9-]+) -->/', $content, $opened);
    preg_match_all('/<!-- #endregion ([a-z0-9-]+) -->/', $content, $closed);

    expect($opened[1])->toBe($closed[1], "Unbalanced regions in {$file}.");

    return $opened[1];
}

it('has the same documentation regions in every README', function (string $readme): void {
    expect(readmeRegions($readme))->toBe(readmeRegions('README.md'));
})->with(fn (): array => array_map('basename', glob(__DIR__.'/../../README.*.md')));

it('includes only regions that exist', function (): void {
    $regions = readmeRegions('README.md');

    foreach (glob(__DIR__.'/../../docs/{,*/}{guide,reference}/*.md', GLOB_BRACE) as $page) {
        preg_match_all('/@include: [.\/]+README(?:\.[a-z]{2})?\.md#([a-z0-9]+(?:-[a-z0-9]+)*)/', file_get_contents($page), $includes);

        foreach ($includes[1] as $region) {
            expect($regions)->toContain($region);
        }
    }
});
