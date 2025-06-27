<?php

namespace App\Filament\Admin\Resources\DiscountResource\Pages;

use App\Filament\Admin\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscount extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()->slideOver(),
        ];
    }
}
