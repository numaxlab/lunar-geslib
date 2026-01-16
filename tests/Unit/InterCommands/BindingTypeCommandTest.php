<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use NumaxLab\Geslib\Lines\BindingType;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn() => Language::factory()->create());

it('creates binding type collection', function () {
    CollectionGroup::factory()->create([
        'handle' => BindingTypeCommand::HANDLE,
    ]);

    $line = BindingType::fromLine([
        BindingType::CODE,
        '123',
        'Tapa dura',
    ]);

    $command = new BindingTypeCommand($line);
    $command();

    $collection = Collection::where('geslib_code', $line->id())
        ->whereHas('group', function ($query) {
            $query->where('handle', BindingTypeCommand::HANDLE);
        })->first();

    expect($collection)
        ->not()->toBeNull()
        ->and($collection->translateAttribute('name'))->toBe('Tapa dura');
});

it('updates existing binding type name', function () {
    $group = CollectionGroup::factory()->create([
        'handle' => BindingTypeCommand::HANDLE,
    ]);

    Collection::factory()->create([
        'collection_group_id' => $group->id,
        'geslib_code' => '123',
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'es' => new Text('Tapa dura'),
            ])),
        ],
    ]);

    $line = BindingType::fromLine([
        BindingType::CODE,
        '123',
        'Tapa blanda',
    ]);

    $command = new BindingTypeCommand($line);
    $command();

    $collection = Collection::where('geslib_code', $line->id())
        ->whereHas('group', function ($query) {
            $query->where('handle', BindingTypeCommand::HANDLE);
        })->first();

    expect($collection)
        ->not()->toBeNull()
        ->and($collection->translateAttribute('name'))->toBe('Tapa blanda');
});
