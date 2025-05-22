<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Type;

class TypeCommand
{
    public const HANDLE = 'product-types';

    public function __invoke(Type $type): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $type->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'geslib-code' => new Text($type->id()),
            'name' => new Text($type->name()),
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
