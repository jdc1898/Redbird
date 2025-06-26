<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Support\Collection;

class ProductCollection extends Collection
{
    protected bool $pricingLoaded = false;

    public function getWithPricing(): static
    {
        if ($this->pricingLoaded) {
            return $this;
        }

        $grouped = collect(Price::all()->data)
            ->map(fn ($raw) => new Price($raw))
            ->groupBy(fn (Price $price) => $price->productId());

        $this->each(function (Product $product) use ($grouped) {
            $product->setPrices($grouped->get($product->id(), collect()));
        });

        $this->pricingLoaded = true;

        return $this;
    }

    public function __get($key)
    {
        return $key === 'withPricing'
            ? $this->getWithPricing()
            : parent::__get($key);
    }

    public function withPricing(): static
    {
        return $this->getWithPricing();
    }
}
