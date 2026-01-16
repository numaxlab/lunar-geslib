<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorBiographyCommand;
use NumaxLab\Lunar\Geslib\Models\Author;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

it('saves biography in author attribute_data', function () {
    $author = Author::factory()->create([
        'geslib_code' => '12345',
    ]);

    $line = AuthorBiography::fromLine([
        AuthorBiography::CODE,
        '12345',
        'Biography',
    ]);

    $command = new AuthorBiographyCommand($line);
    $command();

    $author->refresh();

    expect($author->translateAttribute('biography'))->toBe('Biography');
});
