<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Status;

class StatusCommand extends AbstractCommand
{
    public const HANDLE = 'statuses';

    public function __invoke(Status $status): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('attribute_data->geslib-code->value', $status->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'geslib-code' => new Text($status->id()),
            'name' => new Text($status->name()),
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
