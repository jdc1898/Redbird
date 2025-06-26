<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\PlanResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
