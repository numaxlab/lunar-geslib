<?php

declare(strict_types=1);

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
    public ?string $q = null;

    #[Url]
    public ?int $ti = null;

    #[Url]
    public ?string $tt = null;

    #[Url]
    public ?int $l = null;

    #[Url]
    public ?string $p = null;

    #[Url]
    public ?int $a = null;

    public Collection $results;

    public function mount(): void
    {
        if (! isset($this->q) || ($this->q === null || $this->q === '' || $this->q === '0')) {
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
    public function searchUpdated(?string $q, ?int $ti, ?string $tt, ?int $l, ?string $p, ?int $a): void
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
