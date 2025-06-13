<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Batch;

use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;

abstract class AbstractBatchCommand implements CommandContract
{
    protected array $log = [];

    public function addLog(string $level, string $message): void
    {
        $this->log[] = [
            'level' => $level,
            'message' => $message,
        ];
    }

    public function getLog(): array
    {
        return $this->log;
    }
}
