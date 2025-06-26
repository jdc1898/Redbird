<?php

namespace Fullstack\Redbird\Models;


use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PromoCode extends Model
{
    /** @use HasFactory<\Database\Factories\PromoCodeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'promo_id',
        'active',
        'code',
        'discount_id',
        'user_id',
        'expires_at',
        'max_redemptions',
        'metadata',
        'restrictions',
        'times_redeemed',
        'deleted_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'restrictions' => 'array',
        'times_redeemed' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    public static function getForm(): array
    {
        return [
            Section::make()
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
