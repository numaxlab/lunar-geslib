<?php

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Storefront;

use Lunar\Base\StorefrontSessionInterface;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Currency;

class Controller
{
    protected StorefrontSessionInterface $session;

    public function __construct()
    {
        $currency = Currency::where('default', true)->firstOrFail();
        StorefrontSession::setCurrency($currency);

        $this->session = StorefrontSession::getFacadeRoot();
    }
}
