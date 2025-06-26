<?php

namespace Fullstack\Redbird\Services\Redbird\Traits;

use Fullstack\Redbird\Services\Redbird\Product;
use Stripe\Collection;

trait HasProducts
{
    public function products(array $params = [], array $opts = []): Collection
    {
        return Product::all($params, $opts);
    }
}
