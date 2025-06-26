<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\PriceResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\PriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\Price\PriceController;
use Illuminate\Database\Eloquent\Model;

class EditPrice extends EditRecord
{
    protected static string $resource = PriceResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return PriceController::update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
