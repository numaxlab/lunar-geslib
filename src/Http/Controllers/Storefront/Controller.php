<?php

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Storefront;

use Lunar\Base\StorefrontSessionInterface;
use Lunar\Base\TaxDriver;
use Lunar\Facades\StorefrontSession;
use Lunar\Facades\Taxes;
use Lunar\Models\Contracts\TaxZone;
use Lunar\Models\Currency;

class Controller
{
    protected StorefrontSessionInterface $session;
    protected TaxDriver $taxes;
    protected TaxZone $taxZone;

    public function __construct()
    {
        $currency = Currency::where('default', true)->firstOrFail();
        StorefrontSession::setCurrency($currency);

        $this->session = StorefrontSession::getFacadeRoot();

        $this->taxes = Taxes::driver();

        $this->taxes->setCurrency($currency);

        $this->taxZone = \Lunar\Models\TaxZone::where('default', true)->firstOrFail();
    }
}
