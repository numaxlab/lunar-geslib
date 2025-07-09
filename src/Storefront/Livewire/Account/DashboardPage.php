<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire\Account;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class DashboardPage extends Component
{
    public ?Authenticatable $user;

    public function mount(): void
    {
        $this->user = Auth::user();
    }

    public function render(): View
    {
        return view('lunar-geslib::storefront.livewire.account.dashboard');
    }
}