<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Models\Collection;

class SectionPage extends Page
{
    public Collection $sectionCollection;

    public function mount($slug): void
    {
        $this->fetchUrl(
            slug: $slug,
            type: (new Collection)->getMorphClass(),
            firstOrFail: true,
            eagerLoad: [
                'element.thumbnail',
                'element.products.variants.basePrices',
                'element.products.defaultUrl',
            ],
        );

        $this->sectionCollection = $this->url->element;
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.section.show');
    }
}