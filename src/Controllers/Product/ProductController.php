<?php

namespace Fullstack\Redbird\Controllers\Product;

use Illuminate\Routing\Controller;
use Fullstack\Redbird\Models\Price;
use Fullstack\Redbird\Models\Product;
use Fullstack\Redbird\Services\Redbird\Redbird;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public static function create(array $data): Product
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'active' => 'boolean',
            'description' => 'nullable|string',
            'amount' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'tax_code' => 'required|string',
            'default_price_data' => 'nullable|array',
            'images' => 'nullable|array',
            'tax_behavior' => 'required|string',
            'marketing_features' => 'nullable|array',
            'package_dimensions' => 'nullable|array',
            'shippable' => 'boolean',
            'statement_descriptor' => 'nullable|string|max:22',
            'unit_label' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: '.$validator->errors()->first());
        }

        $product = Product::create($data);

        // Create the default price
        if ($data['billing_type'] === 'recurring' && $data['recurring_pricing_model'] === 'flat-rate') {
            $price = self::recurringFlatRate($data, $product);
        }

        if ($data['billing_type'] === 'recurring' && $data['recurring_pricing_model'] === 'package') {
            $price = self::recurringPackage($data, $product);
        }

        if ($data['billing_type'] === 'recurring' && $data['recurring_pricing_model'] === 'tiered') {
            $price = self::recurringTiered($data, $product);
        }

        if ($data['billing_type'] === 'recurring' && $data['recurring_pricing_model'] === 'usage') {
            $price = self::recurringUsage($data, $product);
        }

        $defaultPrice = Price::create($price);

        $product->default_price = $defaultPrice->id;
        $product->save();

        return $product;
    }

    public static function sync(Product $product): Product
    {
        Redbird::products()->create([
            'name' => $product->name,
            'description' => $product->description,
            'active' => true,
            'metadata' => $product->metadata,
            'product_tax_code' => $product->product_tax_code,
            'features' => $product->features,
            'statement_descriptor' => $product->statement_descriptor,
        ]);

        $prices = $product->prices;

        foreach ($prices as $price) {

        }

        return $product;
    }

    private static function recurringFlatRate(array $data, Product $product): array
    {
        return [
            'active' => false,
            'currency' => 'usd',
            // 'metadata' => [],
            'nickname' => 'Default price for '.$data['name'],
            'product_id' => $product->id,
            'recurring' => [
                'interval' => $data['billing_period'],
                'interval_count' => $data['interval_count'] ?? 1,
            ],
            'tax_behavior' => $data['tax_behavior'],                    // inclusive, exclusive, unspecified
            'unit_amount' => $data['amount'],
            'type' => $data['billing_type'],                             // one-time, recurring
            // 'billing_scheme' => 'per_unit',                              // per_unit, tiered
            // 'currency_options' => null,
            // 'custom_unit_amount' => null,
            // 'livemode' => false,
            // 'lookup_key' => $data['lookup_key'],
            // 'tiers' => null,
            // 'tiers_mode' => null,
            // 'transform_quantity' => null,
            // 'unit_amount_decimal' => $data['amount'],
            'is_synced' => false,                                       // Only set if billing_scheme is per_unit
        ];
    }

    private static function recurringPackage(array $data, Product $product): array
    {
        return [
            'active' => false,
            'currency' => 'usd',
            'nickname' => 'Default price for '.$data['name'],
            'product_id' => $product->id,
            'recurring' => [
                'interval' => $data['billing_period'],
                'interval_count' => $data['interval_count'] ?? 1,
            ],
            'tax_behavior' => $data['tax_behavior'],
            'unit_amount' => $data['amount'],
            'type' => $data['billing_type'],
            'billing_scheme' => 'per_unit',
            'metadata' => [
                'package_type' => 'package',
                'package_units' => $data['package_units'],
                'unit_label' => $data['unit_label'] ?? 'units',
            ],
            'transform_quantity' => [
                'divide_by' => (int) $data['package_units'],
                'round' => 'up',
            ],
            'lookup_key' => $data['lookup_key'] ?? null,
            'is_synced' => false,
        ];
    }

    private static function recurringTiered(array $data, Product $product): array
    {
        $tiers = $data['tiers'] ?? [];

        // Sort tiers by up_to value to ensure proper ordering
        usort($tiers, function ($a, $b) {
            $aValue = $a['last_unit'] === '∞' ? PHP_FLOAT_MAX : (int) $a['last_unit'];
            $bValue = $b['last_unit'] === '∞' ? PHP_FLOAT_MAX : (int) $b['last_unit'];

            return $aValue <=> $bValue;
        });

        // If no infinite tier exists, add one based on the last tier's pricing
        if (! empty($tiers) && $tiers[count($tiers) - 1]['last_unit'] !== '∞') {
            $lastTier = end($tiers);
            $tiers[] = [
                'last_unit' => '∞',
                'per_unit' => $lastTier['per_unit'] ?? '0',
                'flat_fee' => $lastTier['flat_fee'] ?? '0',
            ];
        }

        return [
            'active' => false,
            'currency' => 'usd',
            'nickname' => 'Default price for '.$data['name'],
            'product_id' => $product->id,
            'recurring' => [
                'interval' => $data['billing_period'],
                'interval_count' => $data['interval_count'] ?? 1,
            ],
            'tax_behavior' => $data['tax_behavior'],
            'type' => $data['billing_type'],
            'billing_scheme' => 'tiered',
            'tiers_mode' => $data['tier_type'] ?? 'graduated',
            'tiers' => array_map(function ($tier) {
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
            }, $tiers),
            'lookup_key' => $data['lookup_key'] ?? null,
            'unit_amount' => 0,
            'is_synced' => false,
        ];
    }

    private static function recurringUsage(array $data, Product $product): array
    {
        $basePrice = [
            'active' => false,
            'currency' => 'usd',
            'nickname' => 'Default price for '.$data['name'],
            'product_id' => $product->id,
            'recurring' => [
                'interval' => $data['billing_period'],
                'interval_count' => $data['interval_count'] ?? 1,
                'usage_type' => $data['usage_type'] ?? 'licensed',
                'aggregate_usage' => $data['aggregate_usage'] ?? 'sum',
            ],
            'tax_behavior' => $data['tax_behavior'],
            'type' => $data['billing_type'],
            'is_synced' => false,
            'lookup_key' => $data['lookup_key'] ?? null,
            'metadata' => [
                'usage_type' => $data['usage_type'] ?? 'licensed',
            ],
        ];

        // Handle different usage-based pricing types
        if ($data['usage_based_type'] === 'unit') {
            $basePrice['billing_scheme'] = 'per_unit';
            $basePrice['unit_amount'] = $data['amount'];
        } elseif ($data['usage_based_type'] === 'package') {
            $basePrice['billing_scheme'] = 'per_unit';
            $basePrice['unit_amount'] = $data['amount'];
            $basePrice['transform_quantity'] = [
                'divide_by' => (int) $data['package_units'],
                'round' => 'up',
            ];
            $basePrice['metadata']['package_type'] = 'package';
            $basePrice['metadata']['package_units'] = $data['package_units'];
            $basePrice['metadata']['unit_label'] = $data['unit_label'] ?? 'units';
        } elseif ($data['usage_based_type'] === 'tier') {
            $basePrice['billing_scheme'] = 'tiered';
            $basePrice['tiers_mode'] = $data['tier_type'] ?? 'graduated';
            $basePrice['tiers'] = array_map(function ($tier) {
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
            }, $data['tiers'] ?? []);
        }

        return $basePrice;
    }
}
