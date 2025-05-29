<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Topic;

class TopicCommand extends AbstractCommand
{
    public const HANDLE = 'categories';

    public function __invoke(Topic $topic): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $topic->id())
            ->where('collection_group_id', $group->id)
            ->first();

        $attributeData = [
            'geslib-code' => new Text($topic->id()),
            'name' => new Text($topic->description()),
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
