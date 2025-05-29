<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use NumaxLab\Geslib\Lines\ArticleAuthor;

class ArticleAuthorCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->isBatch = true;
    }

    public function __invoke(ArticleAuthor $articleAuthor): void
    {
        $group = CollectionGroup::where('handle', AuthorCommand::HANDLE)->firstOrFail();

        $author = Collection::where('attribute_data->geslib-code->value', $articleAuthor->authorId())
            ->where('collection_group_id', $group->id)->get();
    }
}
