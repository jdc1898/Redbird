<?php

namespace App\Models;

use App\Observers\PlanObserver;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PlanObserver::class])]
class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'has_trial' => 'boolean',
        'is_default' => 'boolean',
        'interval_count' => 'integer',
        'interval_period' => 'string',
        'unit_amount' => 'integer',
        'product_id' => 'integer',
        'price_id' => 'string',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['display_label'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public static function getForm($productId = null): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->unique(Plan::class, 'name', ignoreRecord: true),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->helperText('Leave empty to generate slug automatically from product name & interval.'),

                    Radio::make('type')
                        ->label('Type')
                        ->options([
                            'flat' => 'Flat Rate',
                            'usage_based' => 'Usage Based',
                        ])
                        ->default('flat')
                        ->required()
                        ->helperText('Flat Rate: Fixed price per interval. Usage Based: Price per unit with optional tiers. Important: Usage-based pricing is not supported for Paddle.'),

                    Select::make('product_id')
                        ->label('Product')
                        ->hidden(function () use ($productId) {
                            return $productId !== null;
                        })
                        ->required()
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload(),

                    TextInput::make('interval_count')
                        ->label('Interval Count')
                        ->default(1)
                        ->numeric()
                        ->required()
                        ->helperText('The number of intervals (weeks, months, etc) between each billing cycle.'),

                    Select::make('interval_period')
                        ->label('Interval Period')
                        ->default('month')
                        ->options([
                            'day' => 'Day',
                            'week' => 'Week',
                            'month' => 'Month',
                            'year' => 'Year',
                        ])
                        ->required()
                        ->helperText('The interval (week, month, etc) between each billing cycle.'),

                    TextInput::make('unit_amount')
                        ->label('Amount')
                        ->maxLength(255)
                        ->required()
                        ->columnSpanFull()
                        ->helperText('This should be set in cents.'),

                    Toggle::make('has_trial')
                        ->required()
                        ->label('Has Trial')
                        ->default(false),

                    Toggle::make('is_active')
                        ->required()
                        ->label('Is Active')
                        ->default(true),

                    Toggle::make('is_default')
                        ->required()
                        ->label('Is Default')
                        ->default(false)
                        ->helperText('When setting the default price, this will be used as the default plan for the product.'),

                    RichEditor::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function tier(): HasOne
    {
        return $this->hasOne(Tier::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        return "{$this->name}\n{$this->description}";
    }

    public function getDescriptionAttribute($value): string
    {
        return "{$this->name}\n".strip_tags($value);
    }
}
