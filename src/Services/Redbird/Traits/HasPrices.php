<?php

namespace Fullstack\Redbird\Services\Redbird\Traits;

use Fullstack\Redbird\Services\Redbird\Redbird;

trait HasPrices
{
    public function prices(array $params = []): array
    {
        return (new Redbird())->prices($params);
    }
}
