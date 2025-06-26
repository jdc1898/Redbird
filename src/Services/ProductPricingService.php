<?php

namespace Fullstack\Redbird\Services;

use Fullstack\Redbird\Models\Product;
use Fullstack\Redbird\Models\Price;

class ProductPricingService
{
    public function createDefaultPrice(Product $product, array $data): Price
    {
        return $product->prices()->create([
            'unit_amount' => $data['unit_amount'],
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['include_tax'] ?? false,
            'default' => true,
            'active' => true,
            'pricing_type' => $data['default_billing_type'],
            'pricing_model' => 'flat-rate',
            'currency' => 'usd',
        ]);
    }

    public function createAdvancedPricing(Product $product, array $data): void
    {
        $pricingType = $data['billing_type'];
        $pricingModel = $data['pricing_model'];

        match ($pricingModel) {
            'customer-defined' => $this->handleCustomerDefinedPricing($product, $data),
            'package' => $this->handlePackagePricing($product, $data),
            'tiered' => $this->handleTieredPricing($product, $data),
            'usage-based' => $this->handleUsageBasedPricing($product, $data),
            default => $this->handleFlatRatePricing($product, $data),
        };
    }

    private function handleCustomerDefinedPricing(Product $product, array $data): void
    {
        $product->prices()->create([
            'pricing_type' => $data['billing_type'],
            'pricing_model' => 'customer-defined',
            'unit_amount' => $data['minimum_amount'] ?? null,
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['tax_behavior'] === 'inclusive',
            'price_description' => $data['price_description'] ?? null,
            'lookup_key' => $data['lookup_key'] ?? null,
            'currency' => 'usd',
            'metadata' => [
                'minimum_amount' => $data['minimum_amount'] ?? null,
                'maximum_amount' => $data['maximum_amount'] ?? null,
                'suggested_amount' => $data['suggested_amount'] ?? null,
            ],
        ]);
    }

    private function handlePackagePricing(Product $product, array $data): void
    {
        $product->prices()->create([
            'pricing_type' => $data['billing_type'],
            'pricing_model' => 'package',
            'unit_amount' => $data['amount'],
            'package_quantity' => $data['package_size'] ?? 1,
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['tax_behavior'] === 'inclusive',
            'price_description' => $data['price_description'] ?? null,
            'lookup_key' => $data['lookup_key'] ?? null,
            'currency' => 'usd',
        ]);
    }

    private function handleTieredPricing(Product $product, array $data): void
    {
        $product->prices()->create([
            'pricing_type' => $data['billing_type'],
            'pricing_model' => 'tiered',
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['tax_behavior'] === 'inclusive',
            'price_description' => $data['price_description'] ?? null,
            'lookup_key' => $data['lookup_key'] ?? null,
            'currency' => 'usd',
            'metadata' => [
                'tiers' => $data['tiers'] ?? [],
                'tier_type' => $data['tier_type'] ?? 'graduated',
            ],
        ]);
    }

    private function handleUsageBasedPricing(Product $product, array $data): void
    {
        $price = $product->prices()->create([
            'pricing_type' => $data['billing_type'],
            'pricing_model' => 'usage-based',
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['tax_behavior'] === 'inclusive',
            'price_description' => $data['price_description'] ?? null,
            'lookup_key' => $data['lookup_key'] ?? null,
            'currency' => 'usd',
            'metadata' => [
                'usage_type' => $data['usage_type'] ?? 'licensed',
                'tiers' => $data['tiers'] ?? [],
            ],
        ]);

        if (isset($data['usage_meter_id'])) {
            $product->usageMeters()->create([
                'name' => $data['usage_meter']['name'],
                'display_name' => $data['usage_meter']['display_name'] ?? $data['usage_meter']['name'],
                'description' => $data['usage_meter']['description'] ?? null,
                'unit_label' => $data['usage_meter']['unit_label'] ?? null,
            ]);
        }
    }

    private function handleFlatRatePricing(Product $product, array $data): void
    {
        $product->prices()->create([
            'pricing_type' => $data['billing_type'],
            'pricing_model' => 'flat-rate',
            'unit_amount' => $data['amount'],
            'billing_period' => $data['billing_period'] ?? null,
            'tax_included' => $data['tax_behavior'] === 'inclusive',
            'price_description' => $data['price_description'] ?? null,
            'lookup_key' => $data['lookup_key'] ?? null,
            'currency' => 'usd',
        ]);
    }
}
