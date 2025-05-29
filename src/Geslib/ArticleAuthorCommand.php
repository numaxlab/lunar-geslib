<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use NumaxLab\Geslib\Lines\ArticleAuthor;

class ArticleAuthorCommand extends AbstractCommand
{
    public function __invoke(ArticleAuthor $articleAuthor): void {}
}
