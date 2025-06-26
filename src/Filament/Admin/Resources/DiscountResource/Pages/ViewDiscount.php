<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\DiscountResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\DiscountResource;
use App\Models\Discount;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDiscount extends ViewRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->form(Discount::getForm())->slideOver(),
        ];
    }
}
