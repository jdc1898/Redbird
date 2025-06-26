<?php

namespace Fullstack\Redbird\Filament\Tenant\Resources\PaymentResource\Pages;

use Fullstack\Redbird\Filament\Tenant\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
