<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Support;

use Lunar\Models\CollectionGroup;

class ImportRegistry
{
    /** @var array<string, CollectionGroup> */
    private static array $collectionGroups = [];

    public static function collectionGroup(string $handle): CollectionGroup
    {
        return self::$collectionGroups[$handle] ??= CollectionGroup::where('handle', $handle)->firstOrFail();
    }

    public static function flush(): void
    {
        self::$collectionGroups = [];
    }
}
