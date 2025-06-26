<?php

namespace Fullstack\Redbird\Services\Redbird\Traits;

use Fullstack\Redbird\Services\Redbird\PriceCollection;
use Fullstack\Redbird\Services\Redbird\PriceManager;
use Fullstack\Redbird\Services\Redbird\ProductManager;
use Fullstack\Redbird\Services\Redbird\ProductUpdater;

trait Redbird
{
    public function product(string $productId): ProductUpdater
    {
        return new ProductUpdater($this, $productId);
    }

    public function products(): ProductManager
    {
        return new ProductManager($this);
    }

    /**
     * Get a price manager for a specific price
     */
    public function price(string $priceId): PriceManager
    {
        return new PriceManager($this, null, $priceId);
    }

    public function prices(): PriceCollection
    {
        return (new PriceManager($this))->all();
    }
}
