<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Livewire\Component;
use Lunar\Models\Url;

abstract class Page extends Component
{
    public ?Url $url = null;

    public function fetchUrl(string $slug, string $type, bool $firstOrFail = false, array $eagerLoad = []): void
    {
        $queryBuilder = Url::whereElementType($type)
            ->whereDefault(true)
            ->whereSlug($slug)
            ->with($eagerLoad);

        $this->url = $firstOrFail ? $queryBuilder->firstOrFail() : $queryBuilder->first();
    }
}
