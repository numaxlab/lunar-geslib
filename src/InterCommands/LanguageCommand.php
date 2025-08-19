<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Language;

class LanguageCommand extends AbstractCommand
{
    public const HANDLE = 'languages';

    public function __construct(private readonly Language $language) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->language->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new Text($this->language->name()),
        ];

        if (! $collection) {
            Collection::create([
                'geslib_code' => $this->language->id(),
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
