<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'type',
        'brand',
        'last_four',
        'exp_month',
        'exp_year',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'payment_method_id' => 'string',
        'type' => 'string',
        'brand' => 'string',
        'last_four' => 'string',
        'exp_month' => 'string',
        'exp_year' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
