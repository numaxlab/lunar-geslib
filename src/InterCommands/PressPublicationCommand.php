<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\InterCommands;

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use NumaxLab\Geslib\Lines\PressPublication;

class PressPublicationCommand extends AbstractCommand
{
    public const BRAND_TYPE = 'press-publication';

    public function __construct(private readonly PressPublication $pressPublication) {}

    public function __invoke(): void
    {
        if ($this->pressPublication->action()->isDelete()) {
            return;
        }

        $brand = Brand::where('geslib_code', $this->pressPublication->id())->first();

        $attributeData = [
            'type' => new Dropdown(self::BRAND_TYPE),
            'country' => new Text($this->pressPublication->countryId()),
        ];

        if (! $brand) {
            Brand::create([
                'geslib_code' => $this->pressPublication->id(),
                'name' => $this->pressPublication->name(),
                'attribute_data' => $attributeData,
            ]);
        } else {
            $brand->update([
                'name' => $this->pressPublication->name(),
                'attribute_data' => $attributeData,
            ]);
        }
    }
}
