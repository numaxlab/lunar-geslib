<?php

namespace NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;

class EditAuthor extends BaseEditRecord
{
    protected static string $resource = AuthorResource::class;

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel::product.pages.edit.title');
    }

    public function getTitle(): string
    {
        return __('lunar-geslib::author.pages.edit.title');
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
                    if ($record->products->count() > 0) {
                        Notification::make()
                            ->warning()
                            ->body(__('lunar-geslib::author.action.delete.notification.error_protected'))
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
