<?php

namespace Fullstack\Redbird\Services\Redbird;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class ProductManager
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function all(array $params = []): ProductCollection
    {
        $stripeProducts = Redbird::stripe()->products->all($params);

        return new ProductCollection(array_map(
            fn ($raw) => new Product($raw),
            $stripeProducts->data
        ));
    }

    public function archive(string $productId): Product
    {
        try {
            $updated = Redbird::stripe()->products->update($productId, [
                'active' => false,
            ]);

            return new Product($updated);
        } catch (ApiErrorException $e) {
            logger()->error('Stripe Product Archive Failed', [
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

    public function price(string $productId): PriceManager
    {
        return new PriceManager($this->user, $productId);
    }

    /**
     * Get all products with pricing loaded.
     */
    public function withPricing(array $params = []): ProductCollection
    {
        return $this->all($params)->withPricing();
    }

    /**
     * Create a new Stripe product after validating input.
     */
    public function create(array $attributes): Product
    {
        $validator = Validator::make($attributes, Product::validationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        try {
            $stripeProduct = Redbird::stripe()->products->create($validated);

            return new Product($stripeProduct);
        } catch (ApiErrorException $e) {
            logger()->error('Stripe Product Creation Failed', [
                'message' => $e->getMessage(),
                'http_status' => $e->getHttpStatus(),
                'stripe_code' => $e->getStripeCode(),
                'param' => $e->getError()->param ?? null,
                'type' => $e->getError()->type ?? null,
                'doc_url' => $e->getError()->doc_url ?? null,
            ]);

            // Optional: rethrow or transform for UI
            throw $e;
        }
    }
}
