<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Language;
use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Geslib\Lines\Author as AuthorLine;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\Models\Author;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

afterEach(fn () => Mockery::close());

it('does nothing when the line action is delete', function () {
    $line = AuthorLine::createWithDeleteAction('A-DEL');

    $command = new AuthorCommand($line);
    $command();

    expect(Author::count())->toBe(0);
});

it('creates a new author when it does not exist', function () {
    $line = AuthorLine::createWithAction(Action::fromCode(Action::ADD), 'A-001', 'Jane Roe');

    $command = new AuthorCommand($line);
    $command();

    $author = Author::where('geslib_code', 'A-001')->first();

    expect($author)
        ->not
        ->toBeNull()
        ->and($author->name)->toBe('Jane Roe')
        ->and($author->attribute_data)->toHaveKey('has-profile-page')
        ->and($author->attribute_data['has-profile-page']->getValue())->toBeTrue();
});

it('updates existing author name', function () {
    $existing = Author::create([
        'geslib_code' => 'A-002',
        'name' => 'Old Name',
    ]);

    $line = AuthorLine::createWithAction(Action::fromCode(Action::MODIFY), 'A-002', 'New Name');

    $command = new AuthorCommand($line);
    $command();

    $existing->refresh();

    expect($existing->name)->toBe('New Name');
});
