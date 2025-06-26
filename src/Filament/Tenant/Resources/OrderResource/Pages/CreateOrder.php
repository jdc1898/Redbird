<?php

namespace Fullstack\Redbird\Filament\Tenant\Resources\OrderResource\Pages;

use Fullstack\Redbird\Filament\Tenant\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
