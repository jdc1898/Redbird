<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'total_after_discount',
        'total_discount',
        'payment_provider',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_amount' => 'integer',
        'total_after_discount' => 'integer',
        'total_discount' => 'integer',
    ];

    protected $with = [
        'user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
