<?php

namespace NumaxLab\Lunar\Geslib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\LogsActivity;

class TrustedStockProvider extends BaseModel
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'geslib_trusted_stock_providers';

    protected $guarded = [];
}
