<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Stripe\Collection;
use Stripe\Product as StripeProduct;

class Product implements Arrayable, Jsonable, JsonSerializable
{
    public array $prices = [];

    public function __construct(public StripeProduct $product) {}

    public function create(array $attributes): Product
    {
        $validator = Validator::make($attributes, Product::validationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $stripeProduct = Redbird::stripe()->products->create($validator->validated());

        return new Product($stripeProduct);
    }

    public static function all($params = null, $opts = null): Collection
    {
        return Redbird::stripe()->products->all($params);
    }

    public function setPrices(iterable $prices): void
    {
        $this->prices = collect($prices)->values()->all();
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    public function prices(): array
    {
        return $this->getPrices();
    }

    /**
     * Convert to JSON-serializable data.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to array using the underlying Stripe product.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'name' => $this->name(),
            'description' => $this->product->description,
            'type' => $this->product->type,
            'active' => $this->product->active,
            'prices' => array_map(
                fn (Price $price) => $price->toArray(),
                $this->prices
            ),
        ];
    }

    /**
     * Convert to JSON string.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function name(): string
    {
        return $this->product->name;
    }

    public function id(): string
    {
        return $this->product->id;
    }

    public static function validationRules(bool $forUpdate = false): array
    {
        return [
            'name' => $forUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'active' => 'boolean',
            'statement_descriptor' => 'nullable|string|max:22',
            'tax_code' => 'nullable|string|starts_with:txcd_',
            'metadata' => 'nullable|array',
            'images' => 'nullable|array',
            'unit_label' => 'nullable|string|max:12',
            'url' => 'nullable|url',
            'marketing_features' => 'nullable|array|max:15',
            'marketing_features.*.name' => 'required_with:marketing_features|string|max:80',
        ];
    }
}
