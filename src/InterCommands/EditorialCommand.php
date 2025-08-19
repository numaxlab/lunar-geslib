<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\Editorial;

class EditorialCommand extends AbstractCommand
{
    public const BRAND_TYPE = 'editorial';

    public function __construct(private readonly Editorial $editorial) {}

    public function __invoke(): void
    {
        if ($this->editorial->action()->isDelete()) {
            $brand = Brand::where('geslib_code', $this->editorial->id())->first();

            if ($brand) {
                $brand->delete();
            }
        } else {
            $brand = Brand::where('geslib_code', $this->editorial->id())->first();

            $attributeData = [
                'type' => new Dropdown(self::BRAND_TYPE),
                'external-name' => new Text($this->editorial->externalName()),
                'country' => new Text($this->editorial->countryId()),
            ];

            if (! $brand) {
                Brand::create([
                    'geslib_code' => $this->editorial->id(),
                    'name' => $this->editorial->name(),
                    'attribute_data' => $attributeData,
                ]);
            } else {
                $brand->update([
                    'name' => $this->editorial->name(),
                    'attribute_data' => $attributeData,
                ]);
            }
        }
    }
}
