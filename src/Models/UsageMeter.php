<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsageMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_synced' => 'boolean',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the meter is synced with Stripe
     */
    public function isSyncedWithStripe(): bool
    {
        return $this->stripe_meter_id !== null && $this->is_active && $this->is_synced;
    }

    /**
     * Get the Stripe meter data for creating/updating
     */
    public function getStripeMeterData(): array
    {
        return [
            'display_name' => $this->display_name,
            'description' => $this->description,
            'unit_label' => $this->unit_label,
            'metadata' => $this->metadata ?? [],
        ];
    }
}
