<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\EmailProviderResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\EmailProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailProvider extends EditRecord
{
    protected static string $resource = EmailProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
