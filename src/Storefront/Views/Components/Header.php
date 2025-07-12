<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Views\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Collection;
use NumaxLab\Lunar\Geslib\Handle;

class Header extends Component
{
    public function __construct() {}

    public function render(): View
    {
        $sectionCollections = Collection::whereHas('group', function ($query) {
            $query->where('handle', Handle::COLLECTION_GROUP_SECTIONS);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->has('defaultUrl')
            ->orderBy('_lft', 'ASC')
            ->with(['defaultUrl'])->get();

        return view('lunar-geslib::storefront.components.header', compact('sectionCollections'));
    }
}