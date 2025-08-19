<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Classification;

class ClassificationCommand extends AbstractCommand
{
    public const HANDLE = 'classifications';

    public function __construct(private readonly Classification $classification) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->classification->id())
            ->where('collection_group_id', $group->id)
            ->first();

        // Falta o 'tipo de artículo' $this->>classification->typeId()
        $attributeData = [
            'name' => new Text($this->classification->name()),
        ];

        if (! $collection) {
            Collection::create([
                'geslib_code' => $this->classification->id(),
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
