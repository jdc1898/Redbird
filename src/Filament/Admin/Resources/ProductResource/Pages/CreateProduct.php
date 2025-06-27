<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Forms\Builders\ProductFormBuilder;
use App\Services\ProductPricingService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getFormSchema(): array
    {
        return ProductFormBuilder::getAdvancedPricingForm();
    }

    protected function getCreateFormAction(): CreateAction
    {
        return parent::getCreateFormAction()
            ->extraAttributes(['data-dusk' => 'create-product-button']);
    }

    protected function afterCreate(): void
    {
        $pricingService = app(ProductPricingService::class);

        // Create default price
        $pricingService->createDefaultPrice($this->record, $this->data);

        // Create advanced pricing if configured
        if (isset($this->data['morePricingOptions'])) {
            $pricingService->createAdvancedPricing($this->record, $this->data['morePricingOptions']);
        }

        // Trigger Stripe sync
        $this->record->triggerStripeSync();
    }
}
