<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Forms\Builders\ProductFormBuilder;
use App\Services\ProductPricingService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getFormSchema(): array
    {
        return ProductFormBuilder::getForm($this->record->id);
    }

    protected function afterSave(): void
    {
        $pricingService = app(ProductPricingService::class);

        // Update default price if it exists
        if ($defaultPrice = $this->record->prices()->where('is_default', true)->first()) {
            $defaultPrice->update([
                'amount' => $this->data['unit_amount'],
                'billing_period' => $this->data['billing_period'] ?? null,
                'include_tax' => $this->data['include_tax'] ?? false,
                'billing_type' => $this->data['default_billing_type'],
            ]);
        } else {
            // Create default price if it doesn't exist
            $pricingService->createDefaultPrice($this->record, $this->data);
        }

        // Handle advanced pricing updates if configured
        if (isset($this->data['morePricingOptions'])) {
            $pricingService->createAdvancedPricing($this->record, $this->data['morePricingOptions']);
        }

        // Trigger Stripe sync
        $this->record->triggerStripeSync();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
