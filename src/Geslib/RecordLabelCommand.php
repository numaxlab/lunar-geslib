<?php

namespace NumaxLab\Lunar\Geslib\Geslib;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\RecordLabel;

class RecordLabelCommand
{
    public const BRAND_TYPE = 'record_label';

    public function __invoke(RecordLabel $record_label): void
    {
        if ($record_label->action()->isDelete()) {
            $brand = Brand::where('attribute_data->geslib-code->value', $record_label->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('attribute_data->geslib-code->value', $record_label->id())->first();

            if (!$brand) {
                Brand::create([
                    'name' => $record_label->name(),
                    'attribute_data' => [
                        'type' => new Dropdown(self::BRAND_TYPE),
                        'geslib-code' => new Number($record_label->id()),
                        'external-name' => new Text($record_label->externalName()),
                        'country' => new Text($record_label->country()),

                    ],
                ]);
            } else {
                $brand->update([
                    'name' => $record_label->name(),
                    'attribute_data' => [
                        'type' => new Dropdown(self::BRAND_TYPE),
                        'geslib-code' => new Number($record_label->id()),
                        'external-name' => new Text($record_label->externalName()),
                        'country' => new Text($record_label->country()),

                    ],
                ]);
            }
        }
    }
}
