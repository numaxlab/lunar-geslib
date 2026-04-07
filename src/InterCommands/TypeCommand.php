<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use NumaxLab\Geslib\Lines\Type;
use NumaxLab\Lunar\Geslib\Support\ImportRegistry;

class TypeCommand extends AbstractCommand
{
    public const HANDLE = 'product-types';

    public function __construct(private readonly Type $geslibType) {}

    public function __invoke(): void
    {
        $group = ImportRegistry::collectionGroup(self::HANDLE);

        $collection = Collection::where('geslib_code', $this->geslibType->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new TranslatedText(collect([
                'es' => new Text($this->geslibType->name()),
            ])),
        ];

        if (! $collection) {
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
