<?php

namespace NumaxLab\Lunar\Geslib\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;

class Date implements FieldType, JsonSerializable
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
        return [
            'options' => [
                'has_time' => 'nullable',
                'options' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if (!json_decode($value, true)) {
                            $fail('Must be valid json');
                        }
                    },
                ],
            ],
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
