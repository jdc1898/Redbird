<?php

namespace Fullstack\Redbird\Filament\Tenant\Resources\PaymentResource\Pages;

use Fullstack\Redbird\Filament\Tenant\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
