<?php

namespace NumaxLab\Lunar\Geslib\Admin\Support\Synthesizers;

use Lunar\Admin\Support\Synthesizers\AbstractFieldSynth;
use NumaxLab\Lunar\Geslib\FieldTypes\Date;

class DateSynth extends AbstractFieldSynth
{
    public static $key = 'lunar_date_field';

    protected static $targetClass = Date::class;
}
