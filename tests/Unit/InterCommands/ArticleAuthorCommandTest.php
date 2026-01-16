<?php

use NumaxLab\Geslib\Lines\ArticleAuthor as ArticleAuthorLine;
use NumaxLab\Geslib\Lines\AuthorType;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleAuthorCommand;
use Tests\TestCase;

uses(TestCase::class);

it('maps data to batch command', function () {
    $line = ArticleAuthorLine::fromLine([
        ArticleAuthorLine::CODE,
        '12345',
        '67890',
        AuthorType::AUTHOR,
        '1',
    ]);

    $command = new ArticleAuthorCommand($line);
    $command();

    expect($command->isBatch)
        ->toBeTrue()
        ->and($command->articleId)->toBe('12345')
        ->and($command->authorId)->toBe('67890')
        ->and($command->authorType)->toBe(AuthorType::AUTHOR)
        ->and($command->position)->toBe(1);
});