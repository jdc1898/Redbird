<?php

namespace Fullstack\Redbird\Filament\Admin\Resources;

use Fullstack\Redbird\Filament\Admin\Resources\ProductResource\Pages;
use Fullstack\Redbird\Filament\Admin\Resources\ProductResource\RelationManagers\PriceRelationManager;
use Fullstack\Redbird\Filament\Admin\Widgets\ProductStatsWidget;
    
use App\Http\Controllers\Product\ProductController;
use App\Jobs\SyncProductWithStripe;
// use Filament\Infolists\Components\Actions;
// use Filament\Infolists\Components\Actions\Action;
use App\Models\Product;
use App\Services\Redbird\Redbird;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ProductResource extends Resource
{
    private const GROUP_STRIPE = 'stripe';

    private const GROUP_ACTIONS = 'actions';

    protected static ?string $model = Product::class;

    // protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 1;

    public static function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_synced', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ProductFormBuilder::getAdvancedPricingForm());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('A product is bundle of features that you offer to your customers.')
            ->description('If you want to provide Basic, Pro and Premium offerings to your customers, create a product for each of them.')
            ->modifyQueryUsing(function (Builder $query, $livewire) {
                $query->withoutGlobalScopes();

                // Handle soft deletes based on tab
                if ($livewire instanceof Pages\ListProducts) {
                    if ($livewire->activeTab === 'archived') {
                        $query->onlyTrashed();
                    } else {
                        $query->withoutTrashed();
                    }
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->size('lg')
                    ->description(function (Product $record) {
                        $description = '';

                        // Add a badge that shows the default price. like $10.00 USD per 85 Units or $10.00 USD per month
                        if ($defaultPrice = $record->defaultPrice) {
                            $description .= view('components.filament.badge-description', [
                                'price' => $defaultPrice->formatForDisplay(),
                                'currency' => Str::upper($defaultPrice->currency),
                                'units' => $defaultPrice->metadata['package_units'] ?? null,
                                'billing_scheme' => $defaultPrice->billing_scheme ?? null,
                                'tiers' => [
                                    'starting_amount' => isset($defaultPrice->tiers[0]['unit_amount']) ? $defaultPrice->tiers[0]['unit_amount'] : null,
                                    'flat_amount' => isset($defaultPrice->tiers[0]['flat_amount']) ? $defaultPrice->tiers[0]['flat_amount'] : null,
                                ],
                                'period' => isset($defaultPrice->recurring['interval']) ? $defaultPrice->recurring['interval'] : null,
                            ])->render();
                        }

                        return new HtmlString($description);
                    })
                    ->searchable(),

                TextColumn::make('inactive_prices')
                    ->label('Inactive Prices')
                    ->description(function (Product $record) {
                        $description = '';

                        // Add inactive prices that can be activated
                        $inactivePrices = $record->prices()->where('active', false)->whereNull('deleted_at')->get();
                        if ($inactivePrices->isNotEmpty()) {
                            foreach ($inactivePrices as $price) {
                                $description .= view('components.filament.price-description', [
                                    'title' => $price->nickname ?? $price->formatForDisplay(),
                                    'subtitle' => $price->formatForDisplay() . ' ' . Str::upper($price->currency) .
                                        (isset($price->recurring['interval']) ? ' per ' . $price->recurring['interval'] : ''),
                                    'description' => isset($price->metadata['package_units']) ?
                                        'Package of ' . $price->metadata['package_units'] . ' units' :
                                        ($price->billing_scheme === 'tiered' ? 'Tiered pricing' : null),
                                    'price' => $price->formatForDisplay(),
                                    'currency' => Str::upper($price->currency),
                                    'units' => $price->metadata['package_units'] ?? null,
                                    'billing_scheme' => $price->billing_scheme ?? null,
                                    'tiers' => [
                                        'starting_amount' => isset($price->tiers[0]['unit_amount']) ? $price->tiers[0]['unit_amount'] : null,
                                        'flat_amount' => isset($price->tiers[0]['flat_amount']) ? $price->tiers[0]['flat_amount'] : null,
                                    ],
                                    'period' => isset($price->recurring['interval']) ? $price->recurring['interval'] : null,
                                ])->render();
                            }
                        }

                        return new HtmlString($description);
                    })
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'all'),

                TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->icon(fn ($state) => $state ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->formatStateUsing(fn ($state) => $state ? 'Live' : 'Inactive')
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'all'),

                TextColumn::make('deleted_at')
                    ->label('Archived Date')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'archived'),

                TextColumn::make('active_subscriptions_count')
                    ->label('Active Subscriptions')
                    ->state(function (Product $record): int {
                        // Get all price IDs for this product
                        $priceIds = $record->prices()->pluck('price_id');

                        // Count active subscriptions through subscription items
                        return \App\Models\Subscription::query()
                            ->whereHas('items', function ($query) use ($priceIds) {
                                $query->whereIn('stripe_price', $priceIds);
                            })
                            ->where('stripe_status', 'active')
                            ->count();
                    })
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('active_prices_count')
                    ->label('Active Prices')
                    ->state(function (Product $record): ?int {
                        $count = $record->prices()
                            ->where('active', true)
                            ->whereNull('deleted_at')
                            ->count();

                        return $count ?: null;
                    })
                    ->url(function (Product $record): string {
                        return PriceResource::getUrl('index', [
                            'tableFilters[product][value]' => $record->id,
                            'tableFilters[active][value]' => '1',
                        ]);
                    })
                    ->placeholder('-')
                    ->weight('semibold')
                    ->color('success')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('monthly_revenue')
                    ->label('Revenue Impact')
                    ->tooltip('Monthly revenue that would be lost if all active subscriptions were cancelled')
                    ->money('usd')
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'archived')
                    ->state(function (Product $record): float {
                        // Get all active subscription IDs for this product
                        $subscriptionIds = $record->subscriptions()
                            ->where('stripe_status', 'active')
                            ->pluck('id');

                        // Calculate average monthly revenue by summing each month separately
                        $monthlyTotals = collect([0, 1, 2])->map(function ($monthsAgo) use ($subscriptionIds) {
                            return \App\Models\Transaction::query()
                                ->whereIn('invoice_id', function ($query) use ($subscriptionIds) {
                                    $query->select('invoice_id')
                                        ->from('invoices')
                                        ->whereIn('subscription_id', $subscriptionIds);
                                })
                                ->where('status', 'succeeded')
                                ->where('transaction_date', '>=', now()->subMonths($monthsAgo)->startOfMonth())
                                ->where('transaction_date', '<=', now()->subMonths($monthsAgo)->endOfMonth())
                                ->sum('amount');
                        });

                        // Return the average of the monthly totals
                        return $monthlyTotals->average() / 100;
                    })
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('latestSyncLog.succeeded_at')
                    ->label('Last Synced')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return 'Not synced';
                        }

                        $date = \Carbon\Carbon::parse($state);

                        // If within last 30 days, show human diff
                        if ($date->diffInDays(now()) <= 30) {
                            return $date->diffForHumans();
                        }

                        // Otherwise show formatted date
                        return $date->format('M j, Y g:i A');
                    })
                    ->sortable()
                    ->alignRight()
                    ->placeholder('Not synced')
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab === 'synced'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->alignRight()
                    ->visible(fn ($livewire) => $livewire instanceof Pages\ListProducts && $livewire->activeTab !== 'synced' && $livewire->activeTab !== 'archived'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activate')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->label('Activate')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Product')
                        ->modalDescription('This will make the product and its default price available. Are you sure you want to continue?')
                        ->modalSubmitActionLabel('Yes, activate product')
                        ->visible(fn ($record, $livewire) => !$record->active && $livewire->activeTab === 'all')
                        ->action(function (Product $record) {
                            try {
                                $record->active = true;
                                $record->save();

                                // Also activate the default price if it exists
                                if ($defaultPrice = $record->defaultPrice) {
                                    $defaultPrice->active = true;
                                    $defaultPrice->save();
                                }

                                // Sync with Stripe if not already synced
                                if (!$record->is_synced) {
                                    SyncProductWithStripe::dispatch($record, 'create');
                                } else {
                                    // If already synced, just update the active status in Stripe
                                    Redbird::resyncProductWithStripe($record);
                                }

                                Notification::make()
                                    ->title('Product activated')
                                    ->body('The product and its default price are now available and synced with Stripe.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error activating product')
                                    ->body('There was an error activating the product: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('activateProduct')
                        ->icon('heroicon-m-arrow-up-circle')
                        ->color('success')
                        ->label('Activate')
                        ->requiresConfirmation()
                        ->modalDescription('This will activate the product. Are you sure you want to continue?')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'inactive')
                        ->action(function (Product $record) {
                            try {
                                $record->active = true;
                                $record->save();

                                Notification::make()
                                    ->title('Product activated')
                                    ->body('The product and its prices have been activated and are now available to your customers.')
                                    ->success()
                                    ->send();

                                return $record;
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to activate product.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('deactivateProduct')
                        ->icon('heroicon-m-arrow-down-circle')
                        ->color('danger')
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Product')
                        ->modalDescription('This will deactivate the product and all its associated prices. Are you sure you want to continue?')
                        ->visible(fn ($record, $livewire) => ($livewire->activeTab === 'all' || $livewire->activeTab === 'active') && $record->active)
                        ->action(function (Product $record) {
                            try {
                                // Deactivate all associated prices first
                                $record->prices()->update(['active' => false]);

                                // Then deactivate the product
                                $record->active = false;
                                $record->save();

                                Notification::make()
                                    ->title('Product deactivated')
                                    ->body('The product and all its prices have been deactivated.')
                                    ->success()
                                    ->send();

                                return $record;
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to deactivate product')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('pushToStripe')
                        ->icon('heroicon-m-arrow-up-circle')
                        ->color('success')
                        ->label('Push to Stripe')
                        ->requiresConfirmation()
                        ->modalDescription('This will create the product and all its prices in Stripe. Are you sure you want to continue?')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'not_synced')
                        ->action(function (Product $record) {
                            try {
                                SyncProductWithStripe::dispatch($record, 'create');

                                Notification::make()
                                    ->title('Product pushed to Stripe')
                                    ->body('The product and its prices have been created in Stripe successfully.')
                                    ->success()
                                    ->send();

                                return $record;
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to push to Stripe')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('resync')
                        ->icon('heroicon-m-arrow-path')
                        ->color('gray')
                        ->label('Resync')
                        ->tooltip('Push this product\'s data to Stripe')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'synced')
                        ->action(function (Product $record) {
                            try {
                                Redbird::resyncProductWithStripe($record);

                                Notification::make()
                                    ->title('Product synced')
                                    ->body('The product has been updated in Stripe successfully.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Sync failed')
                                    ->body('Failed to sync product: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->successRedirectUrl(fn (): string => request()->url()),

                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'all'),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'all'),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'all')
                        ->before(function (Product $record) {
                            // If the product is synced with Stripe, archive it and its prices
                            try {
                                if ($record->is_synced && $record->product_id) {
                                    Redbird::archiveProductInStripe($record);

                                    Notification::make()
                                        ->title('Product and prices archived in Stripe')
                                        ->success()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to archive in Stripe')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Add restore action for archived products
                    Tables\Actions\RestoreAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->visible(fn ($livewire) => $livewire->activeTab === 'archived'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->dropdown(true)
                    ->dropdownPlacement('bottom-start'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Product')
                    ->slideOver()
                    ->modalHeading('Create New Product')
                    ->modalDescription('Create a new product with basic pricing. You can add more complex pricing options after creation.')
                    ->modalWidth('xl')
                    ->using(function (array $data, string $model): Model {
                        return ProductController::create($data);
                    })
                    ->successNotificationTitle('Product created successfully. You can now add more pricing options.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PriceRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Product Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('slug')
                            ->label('Slug'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose prose-invert'])
                            ->html(),

                        TextEntry::make('is_popular')
                            ->label('')
                            ->getStateUsing(function ($record) {
                                return $record->is_popular ? '🔥 Popular Product' : '';
                            })
                            ->badge(
                                fn ($state) => $state ? 'Popular' : 'Not Popular',
                            )
                            ->color(
                                fn ($state) => $state ? 'warning' : 'info',
                            ),
                    ]),

                InfolistSection::make('Pricing')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('plans')
                            ->label('Plans')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($state) {
                                return $state->map(function ($plan) {
                                    $amount = number_format($plan->unit_amount / 100, 2);
                                    $period = match ($plan->billing_period) {
                                        'month' => '/mo',
                                        'year' => '/yr',
                                        'week' => '/wk',
                                        'day' => '/day',
                                        '3months' => '/quarter',
                                        '6months' => '/6mo',
                                        default => ''
                                    };

                                    $type = $plan->billing_type === 'recurring' ? $period : ' one-time';

                                    return "{$plan->name}: \${$amount}{$type}";
                                });
                            }),

                        TextEntry::make('is_synced')
                            ->label('Stripe Status')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Synced' : 'Not Synced'),
                    ]),

                InfolistSection::make('ℹ️ More Information')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        KeyValueEntry::make('metadata'),
                        RepeatableEntry::make('features')
                            ->schema([
                                TextEntry::make('name'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }
}
