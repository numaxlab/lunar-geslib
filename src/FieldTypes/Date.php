<?php

namespace NumaxLab\Lunar\Geslib\FieldTypes;

use Lunar\Base\FieldType;

class Date implements FieldType
{
    protected ?string $value;

    public function __construct($value = '')
    {
        $this->setValue($value);
    }

    public function __toString()
    {
        return $this->getValue() ?? '';
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getConfig(): array
    {
        return [];
    }


}
