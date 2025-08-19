<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Topic;
use NumaxLab\Lunar\Geslib\Handle;

class TopicCommand extends AbstractCommand
{
    public const HANDLE = Handle::COLLECTION_GROUP_TAXONOMIES;

    public function __construct(private readonly Topic $topic) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->topic->id())
            ->where('collection_group_id', $group->id)
            ->first();

        $attributeData = [
            'name' => new Text($this->topic->description()),
        ];

        if (! $collection) {
            Collection::create([
                'geslib_code' => $this->topic->id(),
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
