<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Geslib\Lines\Author as AuthorLine;
use NumaxLab\Lunar\Geslib\Models\Author;

class AuthorCommand extends AbstractCommand
{
    public function __construct(private readonly AuthorLine $author) {}

    public function __invoke(): void
    {
        $author = Author::where('geslib_code', $this->author->id())->first();

        if (!$author) {
            Author::create([
                'geslib_code' => $this->author->id(),
                'name' => $this->author->name(),
            ]);
        } else {
            $author->update([
                'name' => $this->author->name(),
            ]);
        }
    }
}
