<?php

namespace App\Filament\Admin\Resources\RoadmapItemResource\Pages;

use App\Filament\Admin\Resources\RoadmapItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoadmapItem extends EditRecord
{
    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
