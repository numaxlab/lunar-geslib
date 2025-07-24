<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\Author;

class AuthorCommand extends AbstractCommand
{
    public const HANDLE = 'authors';

    public function __construct(private readonly Author $author) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', self::HANDLE)->firstOrFail();

        $collection = Collection::where('geslib_code', $this->author->id())
            ->where('collection_group_id', $group->id)->first();

        $attributeData = [
            'name' => new Text(Str::title($this->author->name())),
        ];

        if (!$collection) {
            Collection::create([
                'geslib_code' => $this->author->id(),
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
