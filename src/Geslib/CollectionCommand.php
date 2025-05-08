<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Collection as EditorialCollection;

class CollectionCommand
{
    public const HANDLE = 'editorial-collections';

    public function __invoke(EditorialCollection $editorial_collection): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = \Lunar\Models\Collection::where('attribute_data->geslib-code->value', $editorial_collection->id())
            ->where('collection_group_id', $group->id)->first();

        if (!$collection) {
            Collection::create([
                'attribute_data' => [
                    'geslib-code' => new Text($editorial_collection->id()),
                    'name' => new Text($editorial_collection->name()),
                ],
                'collection_group_id' => $group->id,
            ]);
        } else {
            $collection->update([
                'attribute_data' => [
                    'geslib-code' => new Text($editorial_collection->id()),
                    'name' => new Text($editorial_collection->name()),
                ],
            ]);
        }
    }
}
