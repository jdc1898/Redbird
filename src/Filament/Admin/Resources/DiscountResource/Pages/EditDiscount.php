<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\DiscountResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
