<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Status;

class StatusCommand extends AbstractCommand
{
    public const HANDLE = 'statuses';

    public function __construct(private readonly Status $status) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->status->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new TranslatedText(collect([
                'es' => new Text($this->status->name()),
            ])),
        ];

        if (! $collection) {
            Collection::create([
                'geslib_code' => $this->status->id(),
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
