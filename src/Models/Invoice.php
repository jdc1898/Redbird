<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'invoice_id',
        'total',
        'paid',
        'billing_period_start',
        'billing_period_end',
    ];

    protected $casts = [
        'paid' => 'boolean',
        'billing_period_start' => 'datetime',
        'billing_period_end' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {

            $user = Auth::user();
            if ($user->stripe_id) {
                $query->where('user_id', Auth::user()->id);
            } else {
                // User does not have a Stripe customer ID
            }
        });
    }
}
