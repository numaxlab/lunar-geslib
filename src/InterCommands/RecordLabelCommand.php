<?php

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\RecordLabel;

class RecordLabelCommand extends AbstractCommand
{
    public const BRAND_TYPE = 'record_label';

    public function __construct(private readonly RecordLabel $recordLabel) {}

    public function __invoke(): void
    {
        if ($this->recordLabel->action()->isDelete()) {
            $brand = Brand::where('geslib_code', $this->recordLabel->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('geslib_code', $this->recordLabel->id())->first();

            $attributeData = [
                'type' => new Dropdown(self::BRAND_TYPE),
                'external-name' => new Text($this->recordLabel->externalName()),
                'country' => new Text($this->recordLabel->country()),

            ];

            if (!$brand) {
                Brand::create([
                    'geslib_code' => $this->recordLabel->id(),
                    'name' => $this->recordLabel->name(),
                    'attribute_data' => $attributeData,
                ]);
            } else {
                $brand->update([
                    'name' => $this->recordLabel->name(),
                    'attribute_data' => $attributeData,
                ]);
            }
        }
    }
}
