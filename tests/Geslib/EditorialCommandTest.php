<?php

namespace NumaxLab\Lunar\Geslib\Tests;

use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Geslib\Lines\Editorial;
use NumaxLab\Lunar\Geslib\Geslib\EditorialCommand;
use PHPUnit\Framework\TestCase;

class EditorialCommandTest extends TestCase
{
    public function testCanCreateEditorial(): void
    {
        $fileLine = Editorial::fromLine([
            '1LB',
            Action::ADD,
            '123',
            'Editorial name',
            'Editorial external name',
            'Spain',
        ]);

        EditorialCommand::handle($fileLine);
    }
}
