<?php

namespace Fullstack\Redbird\Filament\Admin\Resources;

use Fullstack\Redbird\Filament\Admin\Forms\PriceForm;
use Fullstack\Redbird\Filament\Admin\Resources\PriceResource\Pages;
use App\Models\Price;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Http\Controllers\Price\PriceController;
use Illuminate\Database\Eloquent\Model;
use Fullstack\Redbird\Filament\Admin\Widgets\PriceStatsWidget;

class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    protected static ?string $modelLabel = 'Prices';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 4;

    public static function getHeaderWidgets(): array
    {
        return [
            PriceStatsWidget::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(PriceForm::getForm(
            $form->getRecord()?->product_id ?? 0
        ));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Prices are the different amounts of units, packages, or tiers of your product that you offer to your customers.')
            ->description('For example: if you have Starter, Pro and Premium products, you would create a monthly and yearly plans for each of those to offer them in different intervals.')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('product'))
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product & Price Details')
                    ->weight('bold')
                    ->size('lg')
                    ->description(function ($record) {
                        $description = '';

                        $description .= view('components.filament.badge-description', [
                            'price' => $record->formatForDisplay(),
                            'currency' => Str::upper($record->currency),
                            'units' => $record->metadata['package_units'] ?? null,
                            'billing_scheme' => $record->billing_scheme ?? null,
                            'tiers' => [
                                'starting_amount' => $record->tiers[0]['unit_amount'] ?? null,
                                'flat_amount' => $record->tiers[0]['flat_amount'] ?? null,
                            ],
                            'period' => ucfirst($record->recurring['interval'] ?? 'month'),
                        ])->render();

                        return new HtmlString($description);
                    })
                    ->default('No Product')
                    ->searchable(query: function ($query, $search) {
                        return $query->where('nickname', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('active')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record->active ? 'Active' : 'Inactive')
                    ->color(fn ($state): string => $state === 'Active' ? 'success' : 'danger')
                    ->icon(fn ($state): string => $state === 'Active' ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->sortable(),

                Tables\Columns\IconColumn::make('default')
                    ->label('Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->state(fn ($record): bool => $record && $record->product?->defaultPrice?->id === $record->id)
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('Active Subscriptions')
                    ->counts('activeSubscriptions')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('activatePrice')
                        ->icon('heroicon-m-arrow-up-circle')
                        ->color('success')
                        ->label('Activate')
                        ->requiresConfirmation()
                        ->modalDescription('This will activate the price. Are you sure you want to continue?')
                        ->visible(fn ($record) => !$record->active && !$record->trashed() && $record->product?->active)
                        ->action(function ($record) {
                            try {
                                $record->active = true;
                                $record->save();

                                Notification::make()
                                    ->title('Price activated')
                                    ->body('The price is now available to your customers.')
                                    ->success()
                                    ->send();

                                return $record;
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to activate price.')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),


                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->visible(fn ($record) => !$record->trashed()),

                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn ($record) => !$record->trashed()),

                    Tables\Actions\Action::make('archivePrice')
                        ->icon('heroicon-m-archive-box')
                        ->color('danger')
                        ->label('Archive')
                        ->requiresConfirmation()
                        ->modalHeading('Archive Price')
                        ->modalDescription(function ($record) {
                            if ($record->activeSubscriptions()->count() > 0) {
                                return 'This price has active subscriptions. Archiving will prevent new customers from purchasing this price, but existing subscriptions will continue to work. Are you sure you want to continue?';
                            }
                            return 'This will archive the price. Archived prices cannot be purchased by new customers. Are you sure you want to continue?';
                        })
                        ->modalSubmitActionLabel('Yes, archive price')
                        ->visible(fn ($record) => !$record->trashed() && $record->product?->defaultPrice?->id !== $record->id)
                        ->action(function ($record) {
                            try {
                                if ($record->product?->defaultPrice?->id === $record->id) {
                                    Notification::make()
                                        ->title('Cannot archive default price')
                                        ->body('This price is set as the default price for its product. Please set a different default price before archiving.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                // Deactivate first if active
                                if ($record->active) {
                                    $record->active = false;
                                    $record->save();
                                }

                                $record->delete(); // This triggers soft delete

                                $subscriptionCount = $record->activeSubscriptions()->count();
                                $message = $subscriptionCount > 0
                                    ? "The price has been archived. {$subscriptionCount} active subscriptions will continue to work."
                                    : 'The price has been archived and is no longer available to customers.';

                                Notification::make()
                                    ->title('Price archived')
                                    ->body($message)
                                    ->success()
                                    ->send();

                                return $record;
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to archive price')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\RestoreAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->visible(fn ($record) => $record->trashed()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->dropdown(true)
                    ->dropdownPlacement('bottom-start'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Price')
                    ->slideOver()
                    ->using(function (array $data): Model {
                        return PriceController::create($data);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrices::route('/'),
            // 'create' => Pages\CreatePrice::route('/create'),
            // 'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
