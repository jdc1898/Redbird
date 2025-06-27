<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'charge_id',
        'transaction_id',
        'invoice_id',
        'customer_id',
        'payment_method_id',
        'amount',
        'transaction_date',
        'paid',
        'payment_method_type',
        'payment_method_details_card_brand',
        'payment_method_details_card_last4',
        'payment_method_details_card_exp_month',
        'payment_method_details_card_exp_year',
        'payment_method_details_authorization_code',
        'receipt_url',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'integer',
        'paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'event_id' => 'string',
        'charge_id' => 'string',
        'transaction_id' => 'string',
        'invoice_id' => 'string',
        'customer_id' => 'string',
        'payment_method_id' => 'string',
        'payment_method_type' => 'string',
        'payment_method_details_card_brand' => 'string',
        'payment_method_details_card_last4' => 'string',
        'payment_method_details_card_exp_month' => 'string',
        'payment_method_details_card_exp_year' => 'string',
        'payment_method_details_authorization_code' => 'string',
        'receipt_url' => 'string',
        'status' => 'string',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'stripe_id');
    }

    public function getPaymentMethodDetailsAttribute()
    {
        return [
            'type' => $this->payment_method_type,
            'card' => [
                'brand' => $this->payment_method_details_card_brand,
                'last4' => $this->payment_method_details_card_last4,
                'exp_month' => $this->payment_method_details_card_exp_month,
                'exp_year' => $this->payment_method_details_card_exp_year,
                'authorization_code' => $this->payment_method_details_authorization_code,
            ],
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (Auth::user()->stripe_id) {
                $query->where('customer_id', Auth::user()->stripe_id);
            }
        });
    }
}
