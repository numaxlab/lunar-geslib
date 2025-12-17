<?php

namespace Tests\Dummies\Cegal;

use NumaxLab\Cegal\Dto\AvailabilityCollection;

readonly class FakeAvailabilityCollection extends AvailabilityCollection
{
    public static function createFake(array $items): self
    {
        return new self($items);
    }
}
