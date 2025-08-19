<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use NumaxLab\Lunar\Geslib\InterCommands\Contracts\CommandContract;

abstract class AbstractCommand implements CommandContract
{
    public ?string $type = null;

    protected bool $isBatch = false;

    protected array $log = [];

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isBatch(): bool
    {
        return $this->isBatch;
    }

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
