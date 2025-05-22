<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\Editorial;

class EditorialCommand
{
    public const BRAND_TYPE = 'editorial';

    public function __invoke(Editorial $editorial): void
    {
        if ($editorial->action()->isDelete()) {
            $brand = Brand::where('attribute_data->geslib-code->value', $editorial->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('attribute_data->geslib-code->value', $editorial->id())->first();

            $attributeData = [
                'type' => new Dropdown(self::BRAND_TYPE),
                'geslib-code' => new Number($editorial->id()),
                'external-name' => new Text($editorial->externalName()),
                'country' => new Text($editorial->countryId()),
            ];

            if (!$brand) {
                Brand::create([
                    'name' => $editorial->name(),
                    'attribute_data' => $attributeData,
                ]);
            } else {
                $brand->update([
                    'name' => $editorial->name(),
                    'attribute_data' => $attributeData,
                ]);
            }
        }
    }
}
