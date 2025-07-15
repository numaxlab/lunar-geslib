<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Filament\Forms\Components\Field;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Models\Collection as LunarCollection;
use NumaxLab\Lunar\Geslib\Handle;
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
            'in-homepage',
        ],
        LanguageCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        TopicCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        IbicCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        AuthorCommand::HANDLE => [
            'subtitle',
            'in-homepage',
        ],
        BindingTypeCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        StatusCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        ClassificationCommand::HANDLE => [
            'subtitle',
            'description',
            'in-homepage',
        ],
        Handle::COLLECTION_GROUP_FEATURED => [
            'geslib-code',
            'subtitle',
            'description',
            'in-homepage',
        ],
        Handle::COLLECTION_GROUP_SECTIONS => [
            'geslib-code',
            'subtitle',
            'description',
        ],
        Handle::COLLECTION_GROUP_ITINERARIES => [
            'geslib-code',
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
