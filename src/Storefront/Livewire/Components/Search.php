<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Components;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Product;

class Search extends Component
{
    public string $query = '';

    public Collection $results;

    public function mount()
    {
        $this->results = collect();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.components.search');
    }

    public function updatedQuery(): void
    {
        if (empty($this->query)) {
            $this->results = collect();
            return;
        }

        $this->results = Product::search($this->query)
            ->query(fn(Builder $query) => $query->with([
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

    public function search(): void
    {
        if (empty($this->query)) {
            return;
        }

        $this->redirect(route('lunar.geslib.storefront.search', ['q' => $this->query]), true);
    }
}
