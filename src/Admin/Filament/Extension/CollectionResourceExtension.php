<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Filament\Forms\Components\Field;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Models\Collection as LunarCollection;
use NumaxLab\Lunar\Geslib\Handle;
use NumaxLab\Lunar\Geslib\InterCommands\BindingTypeCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\CollectionCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\LanguageCommand;
use NumaxLab\Lunar\Geslib\InterCommands\StatusCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;

class CollectionResourceExtension extends ResourceExtension
{
    private const HIDDEN_FIELDS = [
        TypeCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        LanguageCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        IbicCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        CollectionCommand::HANDLE => [],
        BindingTypeCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        StatusCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        ClassificationCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
        ],
        Handle::COLLECTION_GROUP_TAXONOMIES => [
            'subtitle',
            'description',
        ],
        Handle::COLLECTION_GROUP_FEATURED => [
            'subtitle',
            'description',
            'is-section',
        ],
        Handle::COLLECTION_GROUP_ITINERARIES => [
            'is-section',
        ],
    ];

    public function extendForm(Form $form): Form
    {
        return $form->schema($this->checkAttributesToHide($form->getComponents()));
    }

    private function checkAttributesToHide(array $components): array
    {
        foreach ($components as $component) {
            if ($component instanceof Field) {
                $component->hidden(
                    static function (?Model $record, Get $get) use ($component): bool {
                        if (! $record instanceof \Illuminate\Database\Eloquent\Model || ! $record instanceof LunarCollection) {
                            return false;
                        }

                        $collectionGroup = $record->group;

                        if (! $collectionGroup || ! array_key_exists($collectionGroup->handle, self::HIDDEN_FIELDS)) {
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
                $component->schema($this->checkAttributesToHide($component->getChildComponents()));
            }
        }

        return $components;
    }
}
