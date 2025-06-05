<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use NumaxLab\Geslib\Lines\ArticleTopic;

class ArticleTopicCommand extends AbstractCommand
{
    public string $articleId;
    public string $topicId;

    public function __construct()
    {
        $this->isBatch = true;
        $this->type = ArticleTopic::CODE;
    }

    public function __invoke(ArticleTopic $articleTopic): void
    {
        $this->articleId = $articleTopic->articleId();
        $this->topicId = $articleTopic->topicId();
    }
}
