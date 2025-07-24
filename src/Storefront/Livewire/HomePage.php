<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Collection;
use NumaxLab\Lunar\Geslib\Handle;

class HomePage extends Page
{
    public function render(): View
    {
        $featuredCollections = Collection::whereHas('group', function ($query) {
            $query->where('handle', Handle::COLLECTION_GROUP_FEATURED);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->orderBy('_lft', 'ASC')
            ->with([
                'products' => function ($query) {
                    $query
                        ->channel(StorefrontSession::getChannel())
                        ->customerGroup(StorefrontSession::getCustomerGroups())
                        ->status('published')
                        ->whereHas('productType', function ($query) {
                            $query->where('id', config('lunar.geslib.product_type_id'));
                        });
                },
                'products.variant',
                'products.variant.taxClass',
                'products.defaultUrl',
                'products.urls',
                'products.thumbnail',
                'products.authors',
                'products.prices',
            ])->get();

        $sectionsCollections = Collection::whereHas('group', function ($query) {
            $query->where('handle', Handle::COLLECTION_GROUP_TAXONOMIES);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->where('attribute_data->in-homepage->value', true)
            ->orderBy('_lft', 'ASC')
            ->with([
                'products' => function ($query) {
                    $query
                        ->channel(StorefrontSession::getChannel())
                        ->customerGroup(StorefrontSession::getCustomerGroups())
                        ->status('published')
                        ->whereHas('productType', function ($query) {
                            $query->where('id', config('lunar.geslib.product_type_id'));
                        });
                },
                'products.variant',
                'products.variant.taxClass',
                'products.defaultUrl',
                'products.urls',
                'products.thumbnail',
                'products.authors',
                'products.prices',
            ])->get();

        $itinerariesCollections = Collection::whereHas('group', function ($query) {
            $query->where('handle', Handle::COLLECTION_GROUP_ITINERARIES);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->where('attribute_data->in-homepage->value', true)
            ->orderBy('_lft', 'ASC')
            ->with([
                'products' => function ($query) {
                    $query
                        ->channel(StorefrontSession::getChannel())
                        ->customerGroup(StorefrontSession::getCustomerGroups())
                        ->status('published')
                        ->whereHas('productType', function ($query) {
                            $query->where('id', config('lunar.geslib.product_type_id'));
                        });
                },
                'products.variant',
                'products.variant.taxClass',
                'products.defaultUrl',
                'products.urls',
                'products.thumbnail',
                'products.authors',
                'products.prices',
            ])->get();

        return view(
            'lunar-geslib::storefront.livewire.homepage',
            compact('featuredCollections', 'sectionsCollections', 'itinerariesCollections'),
        );
    }
}
