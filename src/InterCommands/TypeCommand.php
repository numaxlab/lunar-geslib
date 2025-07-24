<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Type;

class TypeCommand extends AbstractCommand
{
    public const HANDLE = 'product-types';

    public function __construct(private readonly Type $geslibType) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->geslibType->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new Text($this->geslibType->name()),
        ];

        if (!$collection) {
            Collection::create([
                'geslib_code' => $this->geslibType->id(),
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
