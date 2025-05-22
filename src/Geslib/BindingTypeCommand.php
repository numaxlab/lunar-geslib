<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\BindingType;

class BindingTypeCommand
{
    public const HANDLE = 'binding-types';

    public function __invoke(BindingType $bindingType): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $bindingType->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'geslib-code' => new Text($bindingType->id()),
            'name' => new Text($bindingType->name()),
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
