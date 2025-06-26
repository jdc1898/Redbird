<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\AnnouncementResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
