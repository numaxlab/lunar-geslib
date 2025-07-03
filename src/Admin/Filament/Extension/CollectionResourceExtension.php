<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Filament\Forms\Components\Field;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Models\Collection as LunarCollection;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;

class CollectionResourceExtension extends ResourceExtension
{
    private const HIDDEN_FIELDS = [
        TypeCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        LanguageCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        TopicCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        IbicCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        AuthorCommand::HANDLE => [
            'subtitle',
        ],
        BindingTypeCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        StatusCommand::HANDLE => [
            'subtitle',
            'description',
        ],
        ClassificationCommand::HANDLE => [
            'subtitle',
            'description',
        ],
    ];

    public function extendForm(Form $form): Form
    {
        return $form->schema($this->checkFieldsToHide($form->getComponents()));
    }

    private function checkFieldsToHide(array $components): array
    {
        foreach ($components as $component) {
            if ($component instanceof Field) {
                $component->hidden(
                    static function (?Model $record, Get $get) use ($component): bool {
                        if (!$record || !$record instanceof LunarCollection) {
                            return false;
                        }

                        $collectionGroup = $record->group;

                        if (!$collectionGroup || !array_key_exists($collectionGroup->handle, self::HIDDEN_FIELDS)) {
                            return false;
                        }

                        return in_array(
                            $component->getName(),
                            self::HIDDEN_FIELDS[$collectionGroup->handle],
                        );
                    },
                );
            }

            if (method_exists($component, 'getChildComponents')) {
                $component->schema($this->checkFieldsToHide($component->getChildComponents()));
            }
        }

        return $components;
    }
}
