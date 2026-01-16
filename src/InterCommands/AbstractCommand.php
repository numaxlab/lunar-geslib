<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;

abstract class AbstractCommand implements CommandContract
{
    public ?string $type = null {
        get {
            return $this->type;
        }
    }

    public bool $isBatch = false {
        get {
            return $this->isBatch;
        }
    }

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
