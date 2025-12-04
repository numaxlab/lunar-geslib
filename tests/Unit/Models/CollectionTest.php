<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\Toggle;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Language;
use NumaxLab\Lunar\Geslib\Models\Collection;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(fn () => Language::factory()->create());

it('checks section tree', function () {
    $collectionGroup = CollectionGroup::factory()->create();

    $childCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Agroecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    expect($childCollection->isInSectionTree())->toBeFalse();

    $parentCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    $childCollection->appendToNode($parentCollection)->save();

    $childCollection->refresh();

    expect($childCollection->isInSectionTree())
        ->toBeFalse()
        ->and($parentCollection->isInSectionTree())->toBeFalse();

    $parentCollection->attribute_data = $parentCollection->attribute_data->merge([
        'is-section' => new Toggle(true),
    ]);

    $parentCollection->save();

    $childCollection->refresh();
    $parentCollection->refresh();

    expect($childCollection->isInSectionTree())
        ->toBeTrue()
        ->and($parentCollection->isInSectionTree())->toBeTrue();
});

it('gets ancestor section', function () {
    $collectionGroup = CollectionGroup::factory()->create();

    $childCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Agroecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    expect($childCollection->getAncestorSection())->toBeNull();

    $parentCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Ecología'),
            ])),
            'is-section' => new Toggle(true),
        ]),
    ]);

    $childCollection->appendToNode($parentCollection)->save();

    $childCollection->refresh();

    expect($childCollection->getAncestorSection())
        ->not()->toBeNull()
        ->toBeInstanceOf(Collection::class)
        ->and($childCollection->getAncestorSection()->id)->toBe($parentCollection->id);
});

it('gets ancestor wrapper', function () {
    $collectionGroup = CollectionGroup::factory()->create();

    $childCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Agroecología [Geslib]'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    expect($childCollection->getAncestorWrapper())->toBeNull();

    $wrapperCollection = Collection::factory()->create([
        'collection_group_id' => $collectionGroup->id,
        'attribute_data' => collect([
            'name' => new TranslatedText(collect([
                'es' => new Text('Agroecología'),
            ])),
            'is-section' => new Toggle(false),
        ]),
    ]);

    $childCollection->appendToNode($wrapperCollection)->save();

    $childCollection->refresh();

    expect($childCollection->getAncestorWrapper())
        ->not()->toBeNull()
        ->toBeInstanceOf(Collection::class)
        ->and($childCollection->getAncestorWrapper()->id)->toBe($wrapperCollection->id);
});
