<?php

use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleTopicCommand;
use Tests\TestCase;

uses(TestCase::class);

it('maps data to batch command', function () {
    $line = ArticleTopic::fromLine([
        ArticleTopic::CODE,
        '12345',
        '67890',
    ]);

    $command = new ArticleTopicCommand($line);
    $command();

    expect($command->isBatch)
        ->toBeTrue()
        ->and($command->topicId)->toBe('12345')
        ->and($command->articleId)->toBe('67890');
});
