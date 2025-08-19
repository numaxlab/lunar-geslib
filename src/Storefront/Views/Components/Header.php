<?php

declare(strict_types=1);

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
        $sectionCollections = Collection::whereHas('group', function ($query): void {
            $query->where('handle', Handle::COLLECTION_GROUP_TAXONOMIES);
        })->whereNull('parent_id')
            ->where('attribute_data->is-section->value', true)
            ->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->has('defaultUrl')
            ->orderBy('_lft', 'ASC')
            ->with(['defaultUrl'])->get();

        return view('lunar-geslib::storefront.components.header', ['sectionCollections' => $sectionCollections]);
    }
}
