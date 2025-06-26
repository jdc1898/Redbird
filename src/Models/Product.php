<?php

namespace Fullstack\Redbird\Models;

use App\Jobs\SyncProductWithStripe;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cashier\Subscription;


class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'name' => 'string',
        'active' => 'boolean',
        'description' => 'string',
        'metadata' => 'array',
        'tax_code' => 'string',
        'images' => 'array',
        'marketing_features' => 'array',
        'package_dimensions' => 'array',
        'shippable' => 'boolean',
        'statement_descriptor' => 'string',
        'unit_label' => 'string',
        'url' => 'string',
        'slug' => 'string',
        'is_synced' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }


    public function defaultPrice(): HasOne
    {
        return $this->hasOne(Price::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getMetadataAttribute($value): array
    {
        return json_decode($value ?? '[]', true);
    }

    public function getFeaturesAttribute($value): array
    {
        return json_decode($value ?? '[]', true);
    }

    public function isSyncedWithStripe(): bool
    {
        return $this->is_synced;
    }

}
