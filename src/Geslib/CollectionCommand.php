<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Collection as EditorialCollection;

class CollectionCommand
{
    public const HANDLE = 'editorial-collections';

    public function __invoke(EditorialCollection $editorialCollection): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $editorialCollection->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'geslib-code' => new Text($editorialCollection->id()),
            'name' => new Text($editorialCollection->name()),
        ];

        if (!$collection) {
            Collection::create([
                'attribute_data' => $attributeData,
                'collection_group_id' => $group->id,
            ]);
        } else {
            $collection->update([
                'attribute_data' => $attributeData,
            ]);
        }
    }
}
