<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Language;

class LanguageCommand
{
    public const HANDLE = 'languages';

    public function __invoke(Language $language): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $language->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'geslib-code' => new Text($language->id()),
            'name' => new Text($language->name()),
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
