<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Geslib\Lines\ArticleTopic;

class ArticleTopicCommand extends AbstractCommand
{
    public string $articleId;
    public string $topicId;

    public function __construct(private readonly ArticleTopic $articleTopic)
    {
        $this->isBatch = true;
        $this->type = ArticleTopic::CODE;
    }

    public function __invoke(): void
    {
        $this->articleId = $this->articleTopic->articleId();
        $this->topicId = $this->articleTopic->topicId();
    }
}
