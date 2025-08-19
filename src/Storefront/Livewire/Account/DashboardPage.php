<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Account;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Contracts\Address;
use Lunar\Models\Contracts\Customer;

class DashboardPage extends Component
{
    public ?Authenticatable $user;

    public ?Customer $customer;

    public ?Address $defaultAddress;

    public Collection $latestOrders;

    public Collection $addresses;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->customer = $this->user?->latestCustomer();
        $this->latestOrders = collect();
        $this->addresses = $this->customer->addresses;
        $this->defaultAddress = $this->addresses->where('shipping_default', true)->first();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.account.dashboard');
    }
}
