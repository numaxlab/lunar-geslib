<?php

namespace NumaxLab\Lunar\Geslib\Storefront\Livewire;

use Illuminate\View\View;
use Lunar\Models\CollectionGroup;
use NumaxLab\Lunar\Geslib\InterCommands\ClassificationCommand;
use NumaxLab\Lunar\Geslib\InterCommands\IbicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TopicCommand;
use NumaxLab\Lunar\Geslib\InterCommands\TypeCommand;

class CollectionListPage extends Page
{
    public function render(): View
    {
        $collectionGroups = CollectionGroup::whereIn('handle', [
            TypeCommand::HANDLE,
            TopicCommand::HANDLE,
            ClassificationCommand::HANDLE,
            IbicCommand::HANDLE,
        ])->orderBy('name')->with(['collections'])->get();

        return view('lunar-geslib::storefront.livewire.collection.index', [
            'collectionGroups' => $collectionGroups,
        ]);
    }
}
