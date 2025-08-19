<?php

namespace NumaxLab\Lunar\Geslib\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Author
{
    public function products(): BelongsToMany;
}
