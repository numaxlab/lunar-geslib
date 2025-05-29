<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

abstract class AbstractCommand
{
    protected bool $isBatch = false;

    public function isBatch(): bool
    {
        return $this->isBatch;
    }
}
