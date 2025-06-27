<?php

namespace App\Filament\Admin\Resources\PlanResource\Pages;

use App\Filament\Admin\Resources\PlanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}
