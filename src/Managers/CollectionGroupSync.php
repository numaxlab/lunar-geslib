<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Managers;

use Illuminate\Support\Collection;
use Lunar\Models\Product;

readonly class CollectionGroupSync
{
    public function __construct(
        private Product $product,
        private int $groupId,
        private Collection $collectionsOfGroup,
    ) {}

    public function handle(): void
    {
        $currentCollections = $this->product->collections()->get();

        $newCollections = $this->collectionsOfGroup->pluck('id')->unique()->values();

        $collectionsToKeep = $currentCollections
            ->filter(fn ($collection): bool => $collection->group_id !== $this->groupId)
            ->pluck('id');

        $this->product->collections()->sync(
            $collectionsToKeep->merge($newCollections)->unique()->toArray(),
        );

        $this->product->searchable();
    }
}
