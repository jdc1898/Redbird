<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Support\Collection;

class PriceCollection extends Collection
{
    /**
     * Filter prices by Stripe product ID.
     */
    public function filterByProduct(string $productId): static
    {
        return $this->filter(fn (Price $price) => $price->productId() === $productId);
    }

    /**
     * Return only active prices.
     */
    public function active(): static
    {
        return $this->filter(fn (Price $price) => $price->isActive());
    }

    /**
     * Return only recurring prices.
     */
    public function recurring(): static
    {
        return $this->filter(fn (Price $price) => $price->isRecurring());
    }

    /**
     * Return array of all prices as arrays.
     */
    public function toArray(): array
    {
        return $this->map(fn (Price $price) => $price->toArray())->all();
    }

    /**
     * Convert to JSON string.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
