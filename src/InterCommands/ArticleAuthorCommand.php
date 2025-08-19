<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Geslib\Lines\ArticleAuthor;

class ArticleAuthorCommand extends AbstractCommand
{
    public string $articleId;

    public string $authorId;

    public string $authorType;

    public int $position;

    public function __construct(private readonly ArticleAuthor $articleAuthor)
    {
        $this->isBatch = true;
        $this->type = ArticleAuthor::CODE;
    }

    public function __invoke(): void
    {
        $this->articleId = $this->articleAuthor->articleId();
        $this->authorId = $this->articleAuthor->authorId();
        $this->authorType = $this->articleAuthor->authorType()->code();
        $this->position = $this->articleAuthor->position();
    }
}
