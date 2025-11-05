<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Extension;

use Filament\Forms;
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
            'is-special',
        ],
        LanguageCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        IbicCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        CollectionCommand::HANDLE => [],
        BindingTypeCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        StatusCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        ClassificationCommand::HANDLE => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        Handle::COLLECTION_GROUP_TAXONOMIES => [
            'subtitle',
            'description',
            'is-special',
        ],
        Handle::COLLECTION_GROUP_FEATURED => [
            'subtitle',
            'description',
            'is-section',
            'is-special',
        ],
        Handle::COLLECTION_GROUP_ITINERARIES => [
            'is-section',
            'is-special',
        ],
    ];

    public function extendForm(Form $form): Form
    {
        return $form->schema(
            array_merge(
                $this->checkAttributesToHide($form->getComponents()),
                [
                    Forms\Components\Section::make('Geslib')->schema([
                        Forms\Components\ViewField::make('geslib_code')
                            ->view('lunar-geslib::filament.forms.components.geslib-code'),
                    ]),
                ],
            ),
        );
    }

    private function checkAttributesToHide(array $components): array
    {
        foreach ($components as $component) {
            if ($component instanceof Field) {
                $component->hidden(
                    static function (?Model $record, Get $get) use ($component): bool {
                        if (!$record instanceof \Illuminate\Database\Eloquent\Model || !$record instanceof LunarCollection) {
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
                $component->schema($this->checkAttributesToHide($component->getChildComponents()));
            }
        }

        return $components;
    }
}
