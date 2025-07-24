<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\BindingType;

class BindingTypeCommand extends AbstractCommand
{
    public const HANDLE = 'binding-types';

    public function __construct(private readonly BindingType $bindingType) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->bindingType->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new Text($this->bindingType->name()),
        ];

        if (!$collection) {
            Collection::create([
                'geslib_code' => $this->bindingType->id(),
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
