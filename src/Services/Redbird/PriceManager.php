<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;

class PriceManager
{
    protected $user;

    protected ?string $productId;

    protected ?string $priceId;

    public function __construct($user = null, ?string $productId = null, ?string $priceId = null)
    {
        $this->user = $user;
        $this->productId = $productId;
        $this->priceId = $priceId;
    }

    public function all(array $params = [])
    {
        $stripePrices = Redbird::stripe()->prices->all($params);

        return array_map(fn ($raw) => $raw, $stripePrices->data);
    }

    public function create(array $attributes)
    {
        if (! isset($attributes['product']) && $this->productId) {
            $attributes['product'] = $this->productId;
        }

        $validator = Validator::make($attributes, $this->rules());

        $validator->validate();

        try {
            $raw = Redbird::stripe()->prices->create($attributes);

            return $raw;
        } catch (ApiErrorException $e) {
            report($e); // optional
            throw new \RuntimeException('Stripe Price Creation Failed', 0, $e);
        }
    }

    public function createMany(array $priceDefinitions)
    {
        $createdPrices = [];

        foreach ($priceDefinitions as $attributes) {

            if (! isset($attributes['product']) && $this->productId) {
                $attributes['product'] = $this->productId;
            }

            $createdPrices[] = $this->create($attributes);
        }

        return $createdPrices;
    }

    /**
     * Archive the current price or a specific price ID.
     *
     * @param  string|null  $priceId  Optional price ID to archive
     * @return Price The archived price
     *
     * @throws \RuntimeException If the price archival fails
     */
    public function archive(?string $priceId = null)
    {
        $priceId = $priceId ?? $this->priceId;

        if (! $priceId) {
            throw new \RuntimeException('No price ID provided for archiving');
        }

        try {
            $raw = Redbird::stripe()->prices->update($priceId, [
                'active' => false,
            ]);

            return $raw;
        } catch (ApiErrorException $e) {
            report($e);
            throw new \RuntimeException('Stripe Price Archive Failed', 0, $e);
        }
    }

    protected function rules(): array
    {
        return [
            'product' => ['required', 'string'],

            'unit_amount' => ['nullable', 'integer'],
            'unit_amount_decimal' => ['nullable', 'string', 'regex:/^\d+(\.\d{1,12})?$/'],

            'currency' => ['required', 'string', 'size:3'],
            'nickname' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],

            'active' => ['nullable', 'boolean'],
            'billing_scheme' => ['nullable', 'in:per_unit,tiered'],

            'recurring' => ['nullable', 'array'],
            'recurring.interval' => ['required_with:recurring', 'in:day,week,month,year'],
            'recurring.interval_count' => ['nullable', 'integer'],
            'recurring.usage_type' => ['nullable', 'in:metered,licensed'],
            'recurring.aggregate_usage' => ['nullable', 'in:last_during_period,last_ever,max,sum'],

            'tiers_mode' => ['nullable', 'in:graduated,volume'],
            'tiers' => ['nullable', 'array'],
            'tiers.*.unit_amount' => ['nullable', 'integer'],
            'tiers.*.unit_amount_decimal' => ['nullable', 'string', 'regex:/^\d+(\.\d{1,12})?$/'],
            'tiers.*.flat_amount' => ['nullable', 'integer'],
            'tiers.*.flat_amount_decimal' => ['nullable', 'string', 'regex:/^\d+(\.\d{1,12})?$/'],
            'tiers.*.up_to' => [
                'required_with:tiers',
                function ($attribute, $value, $fail) {
                    if (! is_int($value) && $value !== 'inf') {
                        $fail("The {$attribute} must be an integer or the string 'inf'.");
                    }
                },
            ],

            'transform_quantity' => ['nullable', 'array'],
            'transform_quantity.divide_by' => ['required_with:transform_quantity', 'integer'],
            'transform_quantity.round' => ['required_with:transform_quantity', 'in:up,down'],

            'custom_unit_amount' => ['nullable', 'array'],
            'custom_unit_amount.maximum' => ['nullable', 'integer'],
            'custom_unit_amount.minimum' => ['nullable', 'integer'],
            'custom_unit_amount.preset' => ['nullable', 'integer'],

            'tax_behavior' => ['nullable', 'in:inclusive,exclusive,unspecified'],
            'lookup_key' => ['nullable', 'string'],
        ];
    }
}
