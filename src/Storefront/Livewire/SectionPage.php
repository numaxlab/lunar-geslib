<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Collection;
use Lunar\Models\Product;

class SectionPage extends Page
{
    use WithPagination;

    public Collection $sectionCollection;

    #[Url]
    public string $q = '';

    #[Url]
    public string $t = '';

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
                'element.children',
            ],
        );

        $this->sectionCollection = $this->url->element;
    }

    public function render(): View
    {
        $queryBuilder = Product::channel(StorefrontSession::getChannel())
            ->customerGroup(StorefrontSession::getCustomerGroups())
            ->status('published')
            ->whereHas('productType', function ($query): void {
                $query->where('id', config('lunar.geslib.product_type_id'));
            })
            ->with([
                'variant',
                'variant.taxClass',
                'authors',
                'images',
                'prices',
                'collections',
                'collections.group',
            ]);

        if ($this->q !== '' && $this->q !== '0') {
            $productsByQuery = Product::search($this->q)->get();

            $queryBuilder->whereIn('id', $productsByQuery->pluck('id'));
        }

        if ($this->t !== '' && $this->t !== '0') {
            $queryBuilder->whereHas('collections', function ($query): void {
                $query->where(
                    (new Collection)->getTable().'.id',
                    (int) $this->t,
                );
            });
        } else {
            $queryBuilder->whereHas('collections', function ($query): void {
                $query->whereIn(
                    (new Collection)->getTable().'.id',
                    $this->sectionCollection->descendants->pluck('id'),
                );
            });
        }

        $products = $queryBuilder->paginate(18);

        return view('lunar-geslib::storefront.livewire.section.show', ['products' => $products])
            ->title($this->sectionCollection->translateAttribute('name'));
    }

    public function search(): void
    {
        $this->resetPage();
    }
}
