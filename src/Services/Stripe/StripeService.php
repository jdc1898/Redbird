<?php

namespace Fullstack\Redbird\Services\Stripe;

use Illuminate\Support\Facades\Log;
use Stripe\PaymentMethod;
use Stripe\StripeClient;
use Throwable;

class StripeService
{
    protected StripeClient $client;

    public function __construct(?string $apiKey = null)
    {
        $this->client = new StripeClient($apiKey ?? config('services.stripe.secret'));
    }

    /**
     * Product
     */
    public function createProduct(array $data): string
    {
        try {
            $product = $this->client->products->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
            ]);

            return $product->id;
        } catch (Throwable $e) {
            Log::error('Stripe Product Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateProduct(string $productId, array $data): string
    {
        try {
            $product = $this->client->products->update($productId, [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            return $product->id;
        } catch (Throwable $e) {
            Log::error('Stripe Product Update Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function archiveProduct(string $productId): void
    {
        try {
            $this->client->products->update($productId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Product Archive Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Price
     */
    public function createPrice(array $data): string
    {
        try {
            $priceData = [
                'currency' => $data['currency'],
                'unit_amount' => $data['unit_amount'],
                'product' => $data['product'],
            ];

            if (! empty($data['interval'])) {
                $priceData['recurring'] = ['interval' => $data['interval']];
            }

            $price = $this->client->prices->create($priceData);

            return $price->id;
        } catch (Throwable $e) {
            Log::error('Stripe Price Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updatePrice(string $priceId, array $data): string
    {
        try {
            $price = $this->client->prices->update($priceId, [
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            return $price->id;
        } catch (Throwable $e) {
            Log::error('Stripe Price Update Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function archivePrice(string $priceId): void
    {
        try {
            $this->client->prices->update($priceId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Price Archive Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function setDefaultPrice(string $productId, string $priceId): void
    {
        try {
            $this->client->products->update($productId, ['default_price' => $priceId]);
        } catch (Throwable $e) {
            Log::error('Stripe Default Price Set Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Customer
     */
    public function createCustomer(array $data): string
    {
        try {
            $customer = $this->client->customers->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'description' => $data['description'] ?? '',
                'metadata' => $data['metadata'] ?? [],
            ]);

            return $customer->id;
        } catch (Throwable $e) {
            Log::error('Stripe Customer Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function removeCustomer(string $customerId): void
    {
        try {
            $this->client->customers->delete($customerId);
        } catch (Throwable $e) {
            Log::error('Stripe Customer Remove Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Subscription
     */
    public function createSubscription(array $data): string
    {
        try {
            $subscription = $this->client->subscriptions->create([
                'customer' => $data['customer_id'],
                'items' => [['price' => $data['price_id']]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return $subscription->id;
        } catch (Throwable $e) {
            Log::error('Stripe Subscription Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Coupons
     */
    public function createCoupon(array $data): string
    {
        try {
            $couponData = [];

            $type = $data['type'] ?? null;
            if ($type === 'percentage') {
                $couponData['percent_off'] = floatval($data['amount_off']);
            } else {
                $couponData['amount_off'] = intval($data['amount_off']);
                $couponData['currency'] = $data['currency'] ?? 'usd';
            }

            $couponData['duration'] = $data['duration'] ? 'forever' : 'once';
            $couponData['name'] = $data['name'] ?? null;


            if (! empty($data['max_redemptions'])) {
                $couponData['max_redemptions'] = $data['max_redemptions'];
            }

            if (! empty($data['valid_until'])) {
                $couponData['redeem_by'] = is_numeric($data['valid_until'])
                    ? intval($data['valid_until'])
                    : strtotime($data['valid_until']);
            }

            $coupon = $this->client->coupons->create($couponData);

            return $coupon->id;
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateCoupon(string $couponId, array $data): string
    {
        try {
            $couponData = [];

            if (isset($data['name'])) {
                $couponData['name'] = $data['name'];
            }

            $coupon = $this->client->coupons->update($couponId, $couponData);

            return $coupon->id;

        } catch (Throwable $e) {
            Log::error('Stripe Coupon Update Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getCouponById(string $couponId): ?object
    {
        try {
            return $this->client->coupons->retrieve($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function archiveCoupon(string $couponId): void
    {
        try {
            $this->client->coupons->delete($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Coupon Archive Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Promotion Codes
     */
    public function createPromotionCode(array $data): string
    {
        try {
            $promoData = [
                'coupon' => $data['coupon'],
                'code' => $data['code'],
            ];

            if (isset($data['customer_id'])) {
                $promoData['customer'] = $data['customer_id'];
            }

            $promoCode = $this->client->promotionCodes->create($promoData);

            return $promoCode->id;
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updatePromotionCode(string $promoCodeId, array $data): string
    {
        try {
            $promoData = [];

            if (isset($data['code'])) {
                $promoData['code'] = $data['code'];
            }

            if (isset($data['active'])) {
                $promoData['active'] = $data['active'];
            }

            if (isset($data['max_redemptions'])) {
                $promoData['max_redemptions'] = intval($data['max_redemptions']);
            }

            if (isset($data['expires_at'])) {
                $promoData['expires_at'] = is_numeric($data['expires_at'])
                    ? intval($data['expires_at'])
                    : strtotime($data['expires_at']);
            }

            $promoCode = $this->client->promotionCodes->update($promoCodeId, $promoData);

            return $promoCode->id;
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Update Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function archivePromotionCode(string $promoCodeId): void
    {
        try {
            $this->client->promotionCodes->update($promoCodeId, ['active' => false]);
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Archive Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPromotionCodeById(string $couponId): ?object
    {
        try {
            return $this->client->promotionCodes->retrieve($couponId);
        } catch (Throwable $e) {
            Log::error('Stripe Promo Code Retrieve Error', ['error' => $e->getMessage()]);

            return null;
        }
    }


    /**
     * Create Test Only Methods
     */
    public function createTestPaymentMethod(array $data): PaymentMethod
    {
        $cards = [
            'tok_visa',
            'tok_mastercard',
            'tok_amex',
        ];

        try {
            $paymentMethod = $this->client->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $data['token'] ?? $cards[array_rand($cards)],
                ],
                'billing_details' => [
                    'name' => $data['name'] ?? 'Test User',
                    'email' => $data['email'] ?? 'sample@example.com',
                ],
            ]);

            return $paymentMethod;
        } catch (Throwable $e) {
            Log::error('Stripe Test Payment Method Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function createTestClock()
    {
        try {
            $clock = $this->client->testHelpers->testClocks->create([
                'frozen_time' => time(),
                'name' => 'Redbird Test Clock - '.now()->toDateTimeString(),
            ]);

            return $clock->id;
        } catch (Throwable $e) {
            Log::error('Stripe Test Clock Create Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function listAllTimeClocks()
    {
        try {
            $clocks = $this->client->testHelpers->testClocks->all();

            return $clocks;
        } catch (Throwable $e) {
            Log::error('Stripe List Test Clocks Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getTestClockById(string $clockId)
    {
        try {
            return $this->client->testHelpers->testClocks->retrieve($clockId);
        } catch (Throwable $e) {
            Log::error('Stripe Get Test Clock Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function archiveTestClock(string $clockId): void
    {
        try {
            $this->client->testHelpers->testClocks->delete($clockId);
        } catch (Throwable $e) {
            Log::error('Stripe Delete Test Clock Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function advanceTimeOnTestClock(string $clockId, int $newTime): ?object
    {
        try {

            // Ensure newTime is a valid timestamp and is in the future based on the current frozen time
            $currentClock = $this->getTestClockById($clockId);
            if (! $currentClock) {
                Log::error('Stripe Advance Test Clock Error', ['error' => 'Clock not found']);

                return null;
            }

            $clock = $this->client->testHelpers->testClocks->advance($clockId, [
                'frozen_time' => $newTime,
            ]);

            return $clock;
        } catch (Throwable $e) {
            Log::error('Stripe Advance Test Clock Error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getCustomerCharges(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $charges = $this->client->charges->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
            ]);

            return $charges->data;
        } catch (Throwable $e) {
            Log::error('Stripe Get Charges Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getCustomerInvoices(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $invoices = $this->client->invoices->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
            ]);

            return $invoices->data;
        } catch (Throwable $e) {
            Log::error('Stripe Get Invoices Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function getFormattedCustomerCharges(string $stripeCustomerId, int $limit = 20): array
    {
        try {
            $charges = $this->client->charges->all([
                'customer' => $stripeCustomerId,
                'limit' => $limit,
                'expand' => ['data.payment_method_details'], // optional but ensures complete data
            ]);

            return collect($charges->data)->map(function ($charge) {
                return [
                    'amount' => $charge->amount / 100,
                    'status' => $charge->status,
                    'payment_method_last4' => $charge->payment_method_details->card->last4 ?? 'N/A',
                    'description' => $charge->description ?? '',
                    'email' => $charge->billing_details->email ?? 'N/A',
                    'date' => \Carbon\Carbon::createFromTimestamp($charge->created),
                    'refunded_at' => $charge->refunded
                        ? \Carbon\Carbon::createFromTimestamp($charge->refunds->data[0]->created ?? $charge->created)
                        : null,
                    'decline_reason' => $charge->failure_message ?? null,
                ];
            })->toArray();
        } catch (Throwable $e) {
            Log::error('Stripe Get Formatted Charges Error', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
