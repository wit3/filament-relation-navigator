<?php

it('ships a Laravel Boost skill whose name matches its folder', function (): void {
    $skills = glob(__DIR__.'/../../resources/boost/skills/*/SKILL.md');

    expect($skills)->not->toBeEmpty();

    foreach ($skills as $skill) {
        $content = file_get_contents($skill);

        expect($content)->toStartWith("---\n")
            ->and(preg_match('/^name: (.+)$/m', $content, $name))->toBe(1)
            ->and(trim($name[1]))->toBe(basename(dirname($skill)))
            ->and($content)->toMatch('/^description: .{40,}$/m');
    }
});
