<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\PaymentProviderResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\PaymentProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentProviders extends ListRecords
{
    protected static string $resource = PaymentProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
