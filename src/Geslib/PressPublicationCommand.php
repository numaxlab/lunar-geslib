<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\PressPublication;

class PressPublicationCommand
{
    public const BRAND_TYPE = 'press-publication';

    public function __invoke(PressPublication $press_publication): void
    {
        if ($press_publication->action()->isDelete()) {
            $brand = Brand::where('attribute_data->geslib-code->value', $press_publication->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('attribute_data->geslib-code->value', $press_publication->id())->first();

            if (!$brand) {
                Brand::create([
                    'name' => $press_publication->name(),
                    'attribute_data' => [
                        'type' => new Dropdown(self::BRAND_TYPE),
                        'geslib-code' => new Number($press_publication->id()),
                        'country' => new Text($press_publication->countryId()),
                    ],
                ]);
            } else {
                $brand->update([
                    'name' => $press_publication->name(),
                    'attribute_data' => [
                        'type' => new Dropdown(self::BRAND_TYPE),
                        'geslib-code' => new Number($press_publication->id()),
                        'country' => new Text($press_publication->countryId()),
                    ],
                ]);
            }
        }
    }
}
