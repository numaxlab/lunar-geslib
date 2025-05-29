<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\PressPublication;

class PressPublicationCommand extends AbstractCommand
{
    public const BRAND_TYPE = 'press-publication';

    public function __invoke(PressPublication $pressPublication): void
    {
        if ($pressPublication->action()->isDelete()) {
            $brand = Brand::where('attribute_data->geslib-code->value', $pressPublication->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('attribute_data->geslib-code->value', $pressPublication->id())->first();

            $attributeData = [
                'type' => new Dropdown(self::BRAND_TYPE),
                'geslib-code' => new Number($pressPublication->id()),
                'country' => new Text($pressPublication->countryId()),
            ];

            if (!$brand) {
                Brand::create([
                    'name' => $pressPublication->name(),
                    'attribute_data' => $attributeData,
                ]);
            } else {
                $brand->update([
                    'name' => $pressPublication->name(),
                    'attribute_data' => $attributeData,
                ]);
            }
        }
    }
}
