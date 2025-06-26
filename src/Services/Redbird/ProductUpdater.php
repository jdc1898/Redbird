<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class ProductUpdater
{
    public function __construct(
        protected $user,
        protected string $productId,
    ) {}

    /**
     * Update the Stripe product.
     */
    public function update(array $attributes): Product
    {
        $validator = Validator::make($attributes, Product::validationRules($forUpdate = true));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $updated = Redbird::stripe()->products->update(
                $this->productId,
                $validator->validated()
            );

            return new Product($updated);
        } catch (ApiErrorException $e) {
            logger()->error('Stripe Product Update Failed', [
                'message' => $e->getMessage(),
                'http_status' => $e->getHttpStatus(),
                'stripe_code' => $e->getStripeCode(),
                'param' => $e->getError()->param ?? null,
                'type' => $e->getError()->type ?? null,
                'doc_url' => $e->getError()->doc_url ?? null,
            ]);

            throw $e;
        }
    }
}
