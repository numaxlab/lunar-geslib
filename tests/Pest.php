<?php

use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Geslib\Lines\Article as ArticleLine;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;

pest()
    ->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

function getSchemaPath(string $filename): string
{
    return __DIR__.'/Schemas/'.$filename;
}

function makeArticleLineMock(array $overrides = []): ArticleLine
{
    $defaults = [
        'id' => 'SKU-123',
        'action' => Action::fromCode(Action::ADD),
        'title' => 'A title',
        'subtitle' => 'A subtitle',
        'createdAt' => now(),
        'noveltyDate' => null,
        'edition' => null,
        'firstEditionYear' => 2020,
        'lastEditionYear' => 2024,
        'originalTitle' => 'Original Title',
        'originalLanguageId' => 'es-ORIG',
        'pagesQty' => 111,
        'illustrationsQty' => 0,
        'editorialId' => 'BR-001',
        'collectionId' => 'COL-01',
        'typeId' => 5,
        'statusId' => 10,
        'languageId' => 'es',
        'taxes' => 1,
        'isbn' => '9780000000000',
        'ean' => '0000000000000',
        'width' => 10,
        'height' => 20,
        'weight' => 30,
        'stock' => 7,
        'priceWithoutTaxes' => 1000,
        'referencePrice' => 1200,
    ];

    $data = array_merge($defaults, $overrides);

    $article = Mockery::mock(ArticleLine::class);

    $article->shouldReceive('id')->andReturn($data['id'])->byDefault();
    $article->shouldReceive('action')->andReturn($data['action'])->byDefault();
    $article->shouldReceive('title')->andReturn($data['title'])->byDefault();
    $article->shouldReceive('subtitle')->andReturn($data['subtitle'])->byDefault();
    $article->shouldReceive('createdAt')->andReturn($data['createdAt'])->byDefault();
    $article->shouldReceive('noveltyDate')->andReturn($data['noveltyDate'])->byDefault();
    $article->shouldReceive('firstEditionYear')->andReturn($data['firstEditionYear'])->byDefault();
    $article->shouldReceive('lastEditionYear')->andReturn($data['lastEditionYear'])->byDefault();
    $article->shouldReceive('originalTitle')->andReturn($data['originalTitle'])->byDefault();
    $article->shouldReceive('originalLanguageId')->andReturn($data['originalLanguageId'])->byDefault();
    $article->shouldReceive('pagesQty')->andReturn($data['pagesQty'])->byDefault();
    $article->shouldReceive('illustrationsQty')->andReturn($data['illustrationsQty'])->byDefault();
    $article->shouldReceive('editorialId')->andReturn($data['editorialId'])->byDefault();
    $article->shouldReceive('collectionId')->andReturn($data['collectionId'])->byDefault();
    $article->shouldReceive('typeId')->andReturn($data['typeId'])->byDefault();
    $article->shouldReceive('statusId')->andReturn($data['statusId'])->byDefault();
    $article->shouldReceive('languageId')->andReturn($data['languageId'])->byDefault();
    $article->shouldReceive('taxes')->andReturn($data['taxes'])->byDefault();
    $article->shouldReceive('isbn')->andReturn($data['isbn'])->byDefault();
    $article->shouldReceive('ean')->andReturn($data['ean'])->byDefault();
    $article->shouldReceive('width')->andReturn($data['width'])->byDefault();
    $article->shouldReceive('height')->andReturn($data['height'])->byDefault();
    $article->shouldReceive('weight')->andReturn($data['weight'])->byDefault();
    $article->shouldReceive('stock')->andReturn($data['stock'])->byDefault();
    $article->shouldReceive('priceWithoutTaxes')->andReturn($data['priceWithoutTaxes'])->byDefault();
    $article->shouldReceive('referencePrice')->andReturn($data['referencePrice'])->byDefault();

    if (array_key_exists('edition', $data) && $data['edition']) {
        $article->shouldReceive('edition')->andReturn($data['edition']);
    } else {
        $article->shouldReceive('edition')->andReturn(null);
    }

    return $article;
}

function ensureCollectionsForArticle(
    ?string $typeId = null,
    ?string $statusId = null,
    ?string $languageId = null,
    ?string $editorialId = null,
    ?string $collectionId = null,
): array {
    $type = null;
    $status = null;
    $language = null;
    $editorial = null;

    if ($typeId !== null) {
        $typeGroup = CollectionGroup::factory(['handle' => TypeCommand::HANDLE])->create();

        $type = Collection::factory()->create([
            'collection_group_id' => $typeGroup->id,
            'geslib_code' => $typeId,
        ]);
    }

    if ($languageId !== null) {
        $langGroup = CollectionGroup::factory(['handle' => LanguageCommand::HANDLE])->create();

        $language = Collection::factory()->create([
            'collection_group_id' => $langGroup->id,
            'geslib_code' => $languageId,
        ]);
    }

    if ($statusId !== null) {
        $statusGroup = CollectionGroup::factory(['handle' => StatusCommand::HANDLE])->create();

        $status = Collection::factory()->create([
            'collection_group_id' => $statusGroup->id,
            'geslib_code' => $statusId,
        ]);
    }

    if ($editorialId !== null && $collectionId !== null) {
        $editorialGroup = CollectionGroup::factory(['handle' => CollectionCommand::HANDLE])->create();

        $editorial = Collection::factory()->create([
            'collection_group_id' => $editorialGroup->id,
            'geslib_code' => CollectionCommand::getGeslibId($editorialId, $collectionId),
        ]);
    }

    return [$type, $status, $language, $editorial];
}
