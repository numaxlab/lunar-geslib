<?php

namespace NumaxLab\Lunar\Geslib\Geslib\Batch;

use Illuminate\Support\Collection;

class ArticleTopicRelation
{
    private Collection $commands;

    public function __construct(Collection $commandsGroupedByArticle)
    {
        $this->commands = $commandsGroupedByArticle;
    }

    public function __invoke()
    {
        foreach ($this->commands as $articleId => $commands) {
            //
        }
    }
}
