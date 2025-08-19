<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Collection;
use NumaxLab\Lunar\Geslib\Handle;

class ItinerariesListPage extends Page
{
    public function render(): View
    {
        $itinerariesCollections = Collection::whereHas('group', function ($query): void {
            $query->where('handle', Handle::COLLECTION_GROUP_ITINERARIES);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->orderBy('_lft', 'ASC')
            ->get();

        return view('lunar-geslib::storefront.livewire.itinerary.index',
            ['itinerariesCollections' => $itinerariesCollections]);
    }
}
