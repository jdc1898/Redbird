<?php

namespace Fullstack\Redbird\Models;

use Fullstack\Redbird\Models\Product;
use Cashier\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    /** @use HasFactory<\Database\Factories\PriceFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'currency' => 'string',
        'metadata' => 'array',
        'nickname' => 'string',
        'product_id' => 'integer',
        'recurring' => 'array',
        'tax_behavior' => 'string',
        'type' => 'string',
        'unit_amount' => 'integer',
        'object' => 'string',
        'billing_scheme' => 'string',
        'currency_options' => 'array',
        'custom_unit_amount' => 'array',
        'livemode' => 'boolean',
        'lookup_key' => 'string',
        'tiers' => 'array',
        'tiers_mode' => 'string',
        'transform_quantity' => 'array',
        'unit_amount_decimal' => 'decimal:2',
        'price_id' => 'string',
        'is_synced' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'stripe_price', 'price_id');
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class, 'stripe_price', 'price_id')
            ->whereNull('ends_at');
    }

    /**
     * Get the formatted amount with currency symbol
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$'.number_format($this->unit_amount / 100, 2);
    }

    /**
     * Check if the price is synced with Stripe
     */
    public function isSyncedWithStripe(): bool
    {
        return $this->stripe_price_id !== null && $this->is_active && $this->is_synced;
    }

    /**
     * Get the Stripe price data for creating/updating
     */
    public function getStripePriceData(): array
    {
        $data = [
            'unit_amount' => $this->unit_amount,
            'currency' => $this->currency,
            'nickname' => $this->nickname,
            'tax_behavior' => $this->tax_included ? 'inclusive' : 'exclusive',
            'metadata' => $this->metadata ?? [],
            'lookup_key' => $this->lookup_key,
        ];

        if ($this->pricing_type === 'recurring') {
            $data['recurring'] = [
                'interval' => $this->billing_period,
                'interval_count' => 1,
            ];
        }

        return $data;
    }

    public function formatForDisplay(): string
    {
        return '$'.number_format($this->unit_amount / 100, 2);
    }
}
