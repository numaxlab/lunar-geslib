<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Lunar\Models\Product;

class SearchPage extends Page
{
    #[Url]
    public ?string $q;

    #[Url]
    public ?int $ti;

    #[Url]
    public ?string $tt;

    #[Url]
    public ?int $l;

    #[Url]
    public ?string $p;

    #[Url]
    public ?int $a;

    public Collection $results;

    public function mount(): void
    {
        if (empty($this->q)) {
            $this->redirect(route('lunar.geslib.storefront.homepage'));

            return;
        }

        $this->search();
    }

    public function search(): void
    {
        $this->results = Product::search($this->q)
            ->query(fn (Builder $query) => $query->with([
                'variant',
                'variant.taxClass',
                'defaultUrl',
                'urls',
                'thumbnail',
                'authors',
                'prices',
            ]))
            ->get();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.search');
    }

    #[On('search-updated')]
    public function searchUpdated($q, $ti, $tt, $l, $p, $a): void
    {
        $this->q = $q;
        $this->ti = $ti;
        $this->tt = $tt;
        $this->l = $l;
        $this->p = $p;
        $this->a = $a;

        $this->search();
    }
}
