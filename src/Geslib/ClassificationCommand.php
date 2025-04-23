<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Classification;

class ClassificationCommand
{
    public const HANDLE = 'classifications';

    public function __invoke(Classification $classification): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        // Falta o 'tipo de artículo' $classification->typeId()
        $collection = Collection::where('attribute_data->geslib-code->value', $classification->id())
            ->where('collection_group_id', $group->id)
            ->first();

        if (!$collection) {
            Collection::create([
                'attribute_data' => [
                    'geslib-code' => new Text($classification->id()),
                    'name' => new Text($classification->name()),
                ],
                'collection_group_id' => $group->id,
            ]);
        } else {
            $collection->update([
                'attribute_data' => [
                    'geslib-code' => new Text($classification->id()),
                    'name' => new Text($classification->name()),
                ],
            ]);
        }
    }
}
