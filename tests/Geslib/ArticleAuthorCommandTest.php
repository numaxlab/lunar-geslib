<?php

namespace NumaxLab\Lunar\Geslib\Tests\Geslib;

use NumaxLab\Lunar\Geslib\Geslib\ArticleAuthorCommand;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class ArticleAuthorCommandTest extends TestCase
{
    /**
     * @test
     */
    public function article_author_command_exists_and_can_be_instantiated()
    {
        $this->assertTrue(class_exists(ArticleAuthorCommand::class));

        $command = new ArticleAuthorCommand();

        $this->assertInstanceOf(ArticleAuthorCommand::class, $command);
    }
}
