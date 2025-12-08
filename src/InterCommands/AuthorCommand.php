<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Toggle;
use NumaxLab\Geslib\Lines\Author as AuthorLine;
use NumaxLab\Lunar\Geslib\Models\Author;

class AuthorCommand extends AbstractCommand
{
    public function __construct(private readonly AuthorLine $author) {}

    public function __invoke(): void
    {
        if ($this->author->action()->isDelete()) {
            return;
        }

        $author = Author::where('geslib_code', $this->author->id())->first();

        $attributeData = [
            'has-profile-page' => new Toggle(true),
        ];

        if (! $author) {
            Author::create([
                'geslib_code' => $this->author->id(),
                'name' => $this->author->name(),
                'attribute_data' => $attributeData,
            ]);
        } else {
            $author->update([
                'name' => $this->author->name(),
                'attribute_data' => array_merge(optional($author->attribute_data)->toArray() ?? [], $attributeData),
            ]);
        }
    }
}
