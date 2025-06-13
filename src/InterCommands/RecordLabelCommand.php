<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\RecordLabel;

class RecordLabelCommand extends AbstractCommand
{
    public const BRAND_TYPE = 'record_label';

    public function __invoke(RecordLabel $recordLabel): void
    {
        if ($recordLabel->action()->isDelete()) {
            $brand = Brand::where('attribute_data->geslib-code->value', $recordLabel->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('attribute_data->geslib-code->value', $recordLabel->id())->first();

            $attributeData = [
                'type' => new Dropdown(self::BRAND_TYPE),
                'geslib-code' => new Number($recordLabel->id()),
                'external-name' => new Text($recordLabel->externalName()),
                'country' => new Text($recordLabel->country()),

            ];

            if (!$brand) {
                Brand::create([
                    'name' => $recordLabel->name(),
                    'attribute_data' => $attributeData,
                ]);
            } else {
                $brand->update([
                    'name' => $recordLabel->name(),
                    'attribute_data' => $attributeData,
                ]);
            }
        }
    }
}
