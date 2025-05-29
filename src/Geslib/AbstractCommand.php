<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

abstract class AbstractCommand
{
    protected $isBatch = false;

    public function isBatch(): bool
    {
        return $this->isBatch;
    }
}
