<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;

class Date implements \Stringable, FieldType, JsonSerializable
{
    protected ?string $value;

    public function __construct($value = '')
    {
        $this->setValue($value);
    }

    public function __toString(): string
    {
        return (string) ($this->getValue() ?? '');
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
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
                    function ($attribute, $value, $fail): void {
                        if (! json_decode($value, true)) {
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
