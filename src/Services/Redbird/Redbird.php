<?php

namespace Fullstack\Redbird\Services\Redbird;

use Stripe\StripeClient;

class Redbird
{
    /**
     * The Redbird library version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The Stripe API version to use.
     */
    const STRIPE_VERSION = '2023-10-16';

    public static string $apiBaseUrl = \Stripe\BaseStripeClient::DEFAULT_API_BASE;

    protected static ?StripeClient $client = null;

    /**
     * Set a custom Stripe client (useful for testing).
     */
    public static function setStripeClient(?StripeClient $client): void
    {
        static::$client = $client;
    }

    public static function stripe(array $options = []): StripeClient
    {
        if (static::$client !== null) {
            return static::$client;
        }

        $config = array_merge([
            'api_key' => $options['api_key'] ?? config('cashier.secret'),
            'stripe_version' => static::STRIPE_VERSION,
            'api_base' => static::$apiBaseUrl,
        ], $options);

        return new StripeClient($config);
    }

    public static function products(array $params = [])
    {
        $stripeProducts = static::stripe()->products->all($params);

        return array_map(fn ($raw) => $raw, $stripeProducts->data);
    }

    public static function prices(array $params = []): array
    {
        $stripePrices = static::stripe()->prices->all($params);

        return array_map(fn ($raw) => $raw, $stripePrices->data);
    }

    /**
     * Push a product and its prices to Stripe
     */
    public static function pushProductToStripe($product): array
    {

        // Prepare product data for Stripe
        $productData = [
            'name' => $product->name,
            'description' => $product->description,
            'metadata' => $product->metadata,
        ];

        // Only include optional fields if they have values
        if (! empty($product->statement_descriptor)) {
            $productData['statement_descriptor'] = $product->statement_descriptor;
        }
        if (! empty($product->unit_label)) {
            $productData['unit_label'] = $product->unit_label;
        }
        if (! empty($product->tax_code)) {
            $productData['tax_code'] = $product->tax_code;
        }

        // Create the product in Stripe
        $stripeProduct = static::stripe()->products->create($productData);

        // Update the local product with the Stripe ID
        $product->update([
            'product_id' => $stripeProduct->id,
            'is_synced' => true,
        ]);

        // Create a sync log entry
        $product->stripeSyncLogs()->create([
            'action' => 'create',
            'status' => 'success',
            'succeeded_at' => now(),
        ]);

        $createdPrices = [];

        // Create prices in Stripe for each local price
        foreach ($product->prices as $price) {
            $createdPrices[] = static::pushPriceToStripe($price, $stripeProduct->id, $product->name);
        }

        return [
            'product' => $stripeProduct,
            'prices' => $createdPrices,
        ];
    }

    /**
     * Push a single price to Stripe
     */
    public static function pushPriceToStripe($price, string $stripeProductId, string $productName): \Stripe\Price
    {
        // Generate a descriptive nickname
        $amount = number_format($price->unit_amount / 100, 2);
        $period = match ($price->billing_period) {
            'month' => '/mo',
            'year' => '/yr',
            'week' => '/wk',
            'day' => '/day',
            '3months' => '/quarter',
            '6months' => '/6mo',
            default => ''
        };

        $nickname = $price->pricing_type === 'recurring'
            ? "{$productName} (\${$amount}{$period})"
            : "{$productName} (\${$amount} one-time)";

        $stripePriceData = [
            'product' => $stripeProductId,
            'unit_amount' => $price->unit_amount,
            'currency' => $price->currency,
            'nickname' => $nickname,
            'tax_behavior' => $price->tax_included ? 'inclusive' : 'exclusive',
            'metadata' => $price->metadata ?? [],
            'lookup_key' => $price->lookup_key,
        ];

        // Only add recurring data if it's a recurring price
        if ($price->pricing_type === 'recurring') {
            $stripePriceData['recurring'] = [
                'interval' => $price->billing_period,
                'interval_count' => 1,
            ];
        }

        // Add tiered pricing configuration if it's a tiered price
        if ($price->pricing_model === 'tiered' && isset($price->metadata['tiers'])) {
            $stripePriceData['billing_scheme'] = 'tiered';
            $stripePriceData['tiers_mode'] = $price->metadata['tier_type'] ?? 'graduated';
            $stripePriceData['tiers'] = array_map(function ($tier) {
                $tierData = [
                    'up_to' => $tier['last_unit'] === '∞' ? 'inf' : (int) $tier['last_unit'],
                ];

                // Convert per_unit amount from dollars to cents
                if (isset($tier['per_unit']) && is_numeric(str_replace(',', '', $tier['per_unit']))) {
                    $perUnit = floatval(str_replace(',', '', $tier['per_unit']));
                    $tierData['unit_amount'] = (int) round($perUnit * 100);
                }

                // Convert flat_fee from dollars to cents
                if (isset($tier['flat_fee']) && is_numeric(str_replace(',', '', $tier['flat_fee']))) {
                    $flatFee = floatval(str_replace(',', '', $tier['flat_fee']));
                    $tierData['flat_amount'] = (int) round($flatFee * 100);
                }

                // Ensure at least one amount is set
                if (! isset($tierData['unit_amount']) && ! isset($tierData['flat_amount'])) {
                    $tierData['unit_amount'] = 0;
                }

                return $tierData;
            }, $price->metadata['tiers']);
        }

        $stripePrice = static::stripe()->prices->create($stripePriceData);

        // Update the local price with the Stripe ID
        $price->update([
            'price_id' => $stripePrice->id,
            'is_synced' => true,
        ]);

        return $stripePrice;
    }

    /**
     * Resync a product with Stripe (update existing product)
     */
    public static function resyncProductWithStripe($product): \Stripe\Product
    {
        // Prepare product data for Stripe
        $productData = [
            'name' => $product->name,
            'description' => $product->description,
            'metadata' => $product->metadata,
        ];

        // Only include optional fields if they have values
        if (! empty($product->statement_descriptor)) {
            $productData['statement_descriptor'] = $product->statement_descriptor;
        }
        if (! empty($product->unit_label)) {
            $productData['unit_label'] = $product->unit_label;
        }
        if (! empty($product->tax_code)) {
            $productData['tax_code'] = $product->tax_code;
        }

        // Update the product in Stripe
        $stripeProduct = static::stripe()->products->update($product->product_id, $productData);

        // Update local sync status
        $product->update([
            'is_synced' => true,
        ]);

        // Create a sync log entry
        $product->stripeSyncLogs()->create([
            'action' => 'update',
            'status' => 'success',
            'succeeded_at' => now(),
        ]);

        // Sync prices that don't have a Stripe ID yet
        foreach ($product->prices as $price) {
            if (! $price->stripe_price_id) {
                static::pushPriceToStripe($price, $product->product_id, $product->name);
            }
        }

        return $stripeProduct;
    }

    /**
     * Archive a product and its prices in Stripe
     */
    public static function archiveProductInStripe($product): void
    {
        if (! $product->is_synced || ! $product->product_id) {
            return;
        }

        try {
            // First, remove the default price from the product
            static::stripe()->products->update($product->product_id, [
                'default_price' => null,
            ]);

            // Then archive all associated prices
            foreach ($product->prices as $price) {
                if ($price->price_id) {
                    static::stripe()->prices->update($price->price_id, [
                        'active' => false,
                    ]);
                }
            }

            // Finally archive the product
            static::stripe()->products->update($product->product_id, [
                'active' => false,
            ]);

            // Create a sync log entry
            $product->stripeSyncLogs()->create([
                'action' => 'archive',
                'status' => 'success',
                'succeeded_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error
            logger()->error('Failed to archive product in Stripe', [
                'product_id' => $product->id,
                'stripe_product_id' => $product->product_id,
                'error' => $e->getMessage(),
            ]);

            // Create a failed sync log entry
            $product->stripeSyncLogs()->create([
                'action' => 'archive',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
