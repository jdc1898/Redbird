<?php

namespace App\Models;

use App\Jobs\SyncProductWithStripe;
use Cashier\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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

    public function usageMeters(): HasMany
    {
        return $this->hasMany(UsageMeter::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
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

    public function stripeSyncLogs(): MorphMany
    {
        return $this->morphMany(StripeSyncLog::class, 'syncable');
    }

    public function latestSyncLog()
    {
        return $this->morphOne(StripeSyncLog::class, 'syncable')
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->latestOfMany();
    }

    public function getSyncStatus(): string
    {
        if (! $this->latestSyncLog) {
            return 'pending';
        }

        if ($this->needsManualSync()) {
            return 'needs_manual_sync';
        }

        return $this->latestSyncLog->status;
    }

    public function needsManualSync(): bool
    {
        $failedSyncs = $this->getFailedSyncs();

        return $failedSyncs->count() >= 3;
    }

    public function getFailedSyncs()
    {
        return $this->stripeSyncLogs()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->where('status', 'failed')
            ->latest()
            ->take(3)
            ->get();
    }

    public function triggerStripeSync(): void
    {
        SyncProductWithStripe::dispatch($this);
    }
}
