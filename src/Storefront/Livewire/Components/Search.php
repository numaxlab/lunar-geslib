<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Components;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Product;
use NumaxLab\Lunar\Geslib\Handle;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;

class Search extends Component
{
    public array $priceRanges = [
        '0-10',
        '10-20',
        '20-30',
        '30-40',
        '40-50',
        '50-1000',
    ];

    public ?string $query = null;

    public ?string $taxonQuery = null;

    public $taxonId;

    public $taxonType;

    public $languageId;

    public $priceRange;

    public $availabilityId;

    public Collection $results;

    public Collection $taxonomies;

    public Collection $languages;

    public Collection $statuses;

    public string $currentRouteName;

    public function mount(): void
    {
        $this->results = collect();
        $this->taxonomies = collect();
        $this->languages = LunarCollection::whereHas('group', function ($query): void {
            $query->where('handle', LanguageCommand::HANDLE);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->get();
        $this->statuses = LunarCollection::whereHas('group', function ($query): void {
            $query->where('handle', StatusCommand::HANDLE);
        })->channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->get();

        if (request()->has('q')) {
            $this->query = request()->input('q');
        }

        $this->currentRouteName = request()->route()->getName();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.components.search');
    }

    public function updatedQuery(): void
    {
        if (! isset($this->query) || ($this->query === null || $this->query === '' || $this->query === '0')) {
            $this->results = collect();

            return;
        }

        if ($this->currentRouteName !== 'lunar.geslib.storefront.search') {
            $this->results = Product::search($this->query)
                ->query(fn (Builder $query) => $query->with([
                    'defaultUrl',
                    'urls',
                    'authors',
                ]))->take(10)->get();
        }
    }

    public function search(): void
    {
        if (! isset($this->query) || ($this->query === null || $this->query === '' || $this->query === '0')) {
            return;
        }

        if ($this->currentRouteName === 'lunar.geslib.storefront.search') {
            $this->dispatch(
                'search-updated',
                q: $this->query,
                ti: $this->taxonId,
                tt: $this->taxonType,
                l: $this->languageId,
                p: $this->priceRange,
                a: $this->availabilityId,
            );
        } else {
            $this->redirect(
                route(
                    'lunar.geslib.storefront.search',
                    [
                        'q' => $this->query,
                        'ti' => $this->taxonId,
                        'tt' => $this->taxonType,
                        'l' => $this->languageId,
                        'p' => $this->priceRange,
                        'a' => $this->availabilityId,
                    ],
                ),
                true,
            );
        }
    }

    public function updatedTaxonQuery(): void
    {
        if (! isset($this->taxonQuery) || ($this->taxonQuery === null || $this->taxonQuery === '' || $this->taxonQuery === '0')) {
            $this->taxonomies = collect();

            return;
        }

        $this->taxonomies = LunarCollection::search($this->taxonQuery)
            ->query(function (Builder $query): void {
                $query
                    ->whereHas('group', function ($query): void {
                        $query->where('handle', Handle::COLLECTION_GROUP_TAXONOMIES);
                    })->channel(StorefrontSession::getChannel())
                    ->customerGroup(StorefrontSession::getCustomerGroups());
            })->get();
    }
}
