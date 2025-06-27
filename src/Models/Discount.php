<?php

namespace App\Models;

use App\Observers\DiscountObserver;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

#[ObservedBy([DiscountObserver::class])]
class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'amount',
        'valid_until',
        'plan_id',
        'has_promo_codes',
        'coupon_id',
        'max_redemptions',
        'max_redemptions_per_user',
        'is_recurring',
        'is_active',
        'duration_in_months',
        'promo_codes',
        'maximum_recurring_intervals',
        'deleted_at',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
        'has_promo_codes' => 'boolean',
        'max_redemptions' => 'integer',
        'max_redemptions_per_user' => 'integer',
        'duration_in_months' => 'integer',
        'promo_codes' => 'array',
        'maximum_recurring_intervals' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class, 'discount_id');
    }

    public static function getForm(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->unique(Discount::class, 'name', ignoreRecord: true),

                    Radio::make('type')
                        ->label('Type')
                        ->required()
                        ->options([
                            'fixed' => 'Fixed Amount',
                            'percentage' => 'Percentage',
                        ])
                        ->default('fixed')
                        ->reactive(),

                    TextInput::make('amount')
                        ->label('Amount')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('If you choose percentage, enter a number between 0 and 100. For example: 90 for 90%. For fixed amount, enter the amount in cents. For example: 1000 for $10.00')
                        ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : 'USD')
                        ->minValue(fn ($get) => 0)
                        ->maxValue(fn ($get) => $get('type') === 'percentage' ? 100 : null)
                        ->rules(function ($get) {
                            return match ($get('type')) {
                                'percentage' => ['numeric', 'min:0', 'max:100'],
                                'fixed' => ['numeric', 'min:0'],
                                default => [],
                            };
                        }),

                    Toggle::make('specific_product')
                        ->label('Apply to Specific Product')
                        ->default(false)
                        ->reactive(),

                    Select::make('plan_id')
                        ->label('Plan')
                        ->relationship('plan', 'name')
                        ->helperText('Select the plans that this discount will be applied to. If you leave empty, discount will be applied to all plans.')
                        ->visible(fn ($get) => $get('specific_product') === true),

                    Select::make('duration')
                        ->label('Duration')
                        ->helperText('For subscriptions and customers, this determines how long this coupon will apply once redeemed. One-time invoices accept both "once" and "forever" coupons.')
                        ->options([
                            'once' => 'Once',
                            'repeating' => 'Multiple Months',
                            'forever' => 'Forever',
                        ])
                        ->default('once')
                        ->reactive(),

                    TextInput::make('maximum_recurring_intervals')
                        ->label('Number of months')
                        ->default(1)
                        ->visible(fn ($get) => $get('duration') === 'repeating'),

                    Checkbox::make('has_redemption_date')
                        ->label('Limit the date range when customers can redeem this discount.')
                        ->default(false)
                        ->reactive(),

                    DateTimePicker::make('valid_until')
                        ->label('Valid Until')
                        ->date()
                        ->default(fn () => now()->format('Y-m-d'))
                        ->visible(fn ($get) => $get('has_redemption_date') === true),

                    Checkbox::make('has_redemption_limit')
                        ->label('Limit the total number of times this discount can be redeemed.')
                        ->default(false)
                        ->reactive(),

                    TextInput::make('max_redemptions')
                        ->label('Max Redemptions')
                        ->default(1)
                        ->helperText('This limit applies across customers so it won\'t prevent a single customer from redeeming multiple times.')
                        ->visible(fn ($get) => $get('has_redemption_limit') === true),

                    Toggle::make('has_promo_codes')
                        ->label('Promo Codes')
                        ->default(false)
                        ->reactive(),

                    Repeater::make('promo_codes')
                        ->label('Promo Codes')
                        ->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->placeholder('PROMO2023')
                                ->helperText('This code is case-insensitive and must be unique across all active promotion codes for any customer. If left blank, a code will be generated automatically.'),

                            Checkbox::make('first_time_transaction')
                                ->label('Eligible for First Time Customers Only')
                                ->default(false),

                            Checkbox::make('specific_customer')
                                ->label('Limit to a specific customer')
                                ->default(false)
                                ->reactive(),

                            Select::make('customer_id')
                                ->label('Customer')
                                ->options(User::where('tenant_id', Auth::user()->id)->pluck('name', 'id'))
                                ->visible(fn ($get) => $get('specific_customer') === true),

                            Checkbox::make('limit_redemption_count')
                                ->label('Limit the number of times the code can be redeemed')
                                ->default(false)
                                ->reactive(),

                            TextInput::make('max_code_redemptions')
                                ->label('')
                                ->suffix('times')
                                ->visible(fn ($get) => $get('limit_redemption_count') === true),

                            Checkbox::make('expiration_date')
                                ->label('Add an expiration date')
                                ->default(false)
                                ->reactive(),

                            DateTimePicker::make('expires_at')
                                ->label('Expires At')
                                ->date()
                                ->default(fn () => now()->format('Y-m-d'))
                                ->visible(fn ($get) => $get('expiration_date') === true),

                            Checkbox::make('requires_minimum_purchase_amount')
                                ->label('Require a minimum purchase amount')
                                ->default(false)
                                ->reactive(),

                            TextInput::make('minimum_purchase_amount')
                                ->label('')
                                ->prefix('$')
                                ->suffix('USD')
                                ->helperText('Please enter this value in cents. For example: 1000 for $10.00')
                                ->visible(fn ($get) => $get('requires_minimum_purchase_amount') === true),

                        ])->visible(fn ($get) => $get('has_promo_codes') === true),
                ]),

            Actions::make([
                Action::make('fill')
                    ->label('Fill with Factory Data')
                    ->icon('heroicon-m-sparkles')
                    ->visible(function (string $operation) {
                        if ($operation != 'create') {
                            return false;
                        }

                        if (config('app.env') !== 'local') {
                            return false;
                        }

                        return true;
                    })
                    ->action(function ($livewire) {
                        $data = Discount::factory()->make()->toArray();
                        $data['duration'] = 'repeating';
                        $data['has_redemption_date'] = true;
                        $data['has_redemption_limit'] = true;
                        $data['max_redemptions'] = 10;
                        logger('', ['data' => $data]);
                        $livewire->form->fill($data);
                    }),
            ]),
        ];
    }
}
