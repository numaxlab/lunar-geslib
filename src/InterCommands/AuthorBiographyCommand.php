<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\AuthorBiography;

class AuthorBiographyCommand extends AbstractCommand
{
    public function __construct(private readonly AuthorBiography $authorBiography) {}

    public function __invoke(): void
    {
        $group = CollectionGroup::where('handle', AuthorCommand::HANDLE)->firstOrFail();

        $author = Collection::where('geslib_code', $this->authorBiography->authorId())
            ->where('collection_group_id', $group->id)->first();

        if (!$author) {
            return;
        }

        $author->update([
            'attribute_data' => array_merge($author->attribute_data->toArray(), [
                'description' => new Text(Str::title($this->authorBiography->biography())),
            ]),
        ]);
    }
}
