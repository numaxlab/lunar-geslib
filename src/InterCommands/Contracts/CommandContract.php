<?php

namespace NumaxLab\Lunar\Geslib\InterCommands\Contracts;

interface CommandContract
{
    public const LEVEL_INFO = 'info';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_ERROR = 'error';

    public function addLog(string $level, string $message): void;

    public function getLog(): array;
}
