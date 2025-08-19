<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Models\Collection;

class ItineraryPage extends Page
{
    public Collection $itineraryCollection;

    public function mount(string $slug): void
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

        $this->itineraryCollection = $this->url->element;
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.itinerary.show');
    }
}
