<?php

namespace NumaxLab\Lunar\Geslib\Admin\Support\FieldTypes;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Lunar\Admin\Support\FieldTypes\BaseFieldType;
use Lunar\Models\Attribute;
use NumaxLab\Lunar\Geslib\Admin\Support\Synthesizers\DateSynth;

class DateField extends BaseFieldType
{
    protected static string $synthesizer = DateSynth::class;

    public static function getConfigurationFields(): array
    {
        return [
            Toggle::make('has_time')->label(
                __('lunar-geslib::fieldtypes.text.form.has_time.label'),
            ),
        ];
    }

    public static function getFilamentComponent(Attribute $attribute): Component
    {
        if ($attribute->configuration && $attribute->configuration->get('has_time')) {
            return DateTimePicker::make($attribute->handle)
                ->dehydrateStateUsing(function ($state) {
                    return $state;
                })
                ->when(
                    filled($attribute->validation_rules),
                    fn(DateTimePicker $component) => $component->rules($attribute->validation_rules),
                )
                ->required((bool)$attribute->required)
                ->helperText($attribute->translate('description'));
        }

        return DatePicker::make($attribute->handle)
            ->dehydrateStateUsing(function ($state) {
                return $state;
            })
            ->when(
                filled($attribute->validation_rules),
                fn(DatePicker $component) => $component->rules($attribute->validation_rules),
            )
            ->required((bool)$attribute->required)
            ->helperText($attribute->translate('description'));
    }
}
