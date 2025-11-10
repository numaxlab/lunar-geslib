<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
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
                'biography' => new TranslatedText(collect([
                    'es' => new Text($this->authorBiography->biography()),
                ])),
            ]),
        ]);
    }
}
