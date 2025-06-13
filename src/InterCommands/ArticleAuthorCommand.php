<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Geslib\Lines\ArticleAuthor;

class ArticleAuthorCommand extends AbstractCommand
{
    public string $articleId;
    public string $authorId;
    public string $authorType;
    public int $position;

    public function __construct()
    {
        $this->isBatch = true;
        $this->type = ArticleAuthor::CODE;
    }

    public function __invoke(ArticleAuthor $articleAuthor): void
    {
        $this->articleId = $articleAuthor->articleId();
        $this->authorId = $articleAuthor->authorId();
        $this->authorType = $articleAuthor->authorType()->code();
        $this->position = $articleAuthor->position();
    }
}
