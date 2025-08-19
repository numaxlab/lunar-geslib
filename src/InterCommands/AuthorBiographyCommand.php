<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Lunar\Geslib\Models\Author;

class AuthorBiographyCommand extends AbstractCommand
{
    public function __construct(private readonly AuthorBiography $authorBiography) {}

    public function __invoke(): void
    {
        $author = Author::where('geslib_code', $this->authorBiography->authorId())->first();

        if (! $author) {
            return;
        }

        $author->update([
            'attribute_data' => array_merge(optional($author->attribute_data)->toArray() ?? [], [
                'biography' => new Text(Str::title($this->authorBiography->biography())),
            ]),
        ]);
    }
}
