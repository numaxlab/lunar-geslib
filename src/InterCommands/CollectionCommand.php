<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Collection as EditorialCollection;

class CollectionCommand extends AbstractCommand
{
    public const HANDLE = 'editorial-collections';

    public function __construct(private readonly EditorialCollection $editorialCollection) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->editorialCollection->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new Text($this->editorialCollection->name()),
        ];

        if (!$collection) {
            Collection::create([
                'geslib_code' => $this->editorialCollection->id(),
                'attribute_data' => $attributeData,
                'collection_group_id' => $group->id,
            ]);
        } else {
            $collection->update([
                'attribute_data' => $attributeData,
            ]);
        }

        $brand = Brand::where('geslib_code', $this->editorialCollection->editorialId())->first();

        if ($brand) {
            $collection->brands()->sync([$brand->id]);
        }
    }
}
