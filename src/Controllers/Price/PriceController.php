<?php

namespace Fullstack\Redbird\Controllers\Price;

use Fullstack\Redbird\Models\Price;
use Fullstack\Redbird\Models\Product;
use Fullstack\Redbird\Services\Redbird\Redbird;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceController extends Controller
{
    public static function create(array $data): Price
    {
        Log::info('PriceController::create called with data:', [
            'raw_data' => $data,
        ]);

        // If we have tiers, force the billing scheme to tiered
        if (isset($data['tiers']) && !empty($data['tiers'])) {
            $data['billing_scheme'] = 'tiered';
        }

        $validator = Validator::make($data, [
            'product_id' => 'required|exists:products,id',
            'nickname' => 'required|string|max:255',
            'type' => 'required|in:recurring,one_time',
            'amount' => 'required_unless:billing_scheme,tiered|numeric|min:0',
            'billing_period' => 'required_if:type,recurring|in:day,week,month,year',
            'tax_behavior' => 'required|in:inclusive,exclusive,unspecified',
            'billing_scheme' => 'required|in:per_unit,tiered',
            'metadata' => 'nullable|array',
            'lookup_key' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('PriceController::create validation failed:', [
                'errors' => $validator->errors()->toArray(),
            ]);
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $transformedData = self::transformData($data);

        Log::info('PriceController::create transformed data:', [
            'transformed_data' => $transformedData,
        ]);

        return Price::create($transformedData);
    }

    private static function transformData(array $data): array
    {
        // Convert amount to unit_amount in cents
        if (isset($data['amount'])) {
            $data['unit_amount'] = (int) ($data['amount'] * 100);
            unset($data['amount']);
        }

        // Handle recurring data
        if ($data['type'] === 'recurring' && isset($data['billing_period'])) {
            $data['recurring'] = [
                'interval' => $data['billing_period'],
                'interval_count' => 1,
            ];
        }

        // Handle package pricing
        if (isset($data['package_units']) && $data['package_units'] > 0) {
            $data['metadata'] = array_merge($data['metadata'] ?? [], [
                'unit_label' => $data['unit_label'] ?? 'unit',
                'package_type' => 'package',
                'package_units' => $data['package_units'],
            ]);

            $data['transform_quantity'] = [
                'round' => 'up',
                'divide_by' => $data['package_units'],
            ];

            unset($data['package_units'], $data['unit_label']);
        }

        // Handle tiered pricing
        if (isset($data['tiers']) && !empty($data['tiers'])) {
            $data['billing_scheme'] = 'tiered';
            $data['tiers'] = array_map(function ($tier) {
                return [
                    'up_to' => $tier['last_unit'] === '∞' ? 'inf' : (int) $tier['last_unit'],
                    'unit_amount' => isset($tier['per_unit']) ? (int) ($tier['per_unit'] * 100) : 0,
                    'flat_amount' => isset($tier['flat_fee']) ? (int) ($tier['flat_fee'] * 100) : 0,
                ];
            }, $data['tiers']);

            // For tiered pricing, unit_amount should be 0
            $data['unit_amount'] = 0;
        }

        // Remove form control fields
        unset(
            $data['billing_period'],
            $data['recurring_pricing_model'],
            $data['one_off_pricing_model'],
            $data['tier_type']
        );

        // Set default values
        $data['currency'] = $data['currency'] ?? 'usd';
        $data['active'] = false; // Always create as inactive
        $data['is_default'] = false; // Never set as default when creating

        return $data;
    }

    public static function update(Price $price, array $data): Price
    {
        Log::info('PriceController::update called:', [
            'price_id' => $price->id,
            'data' => $data,
        ]);

        $transformed = static::transformData($data);
        $price->update($transformed);

        return $price->fresh();
    }
}
