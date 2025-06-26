<?php

namespace Fullstack\Redbird\Filament\Admin\Resources;

use Fullstack\Redbird\Filament\Admin\Resources\DiscountResource\Pages;
use Fullstack\Redbird\Filament\Admin\Resources\DiscountResource\RelationManagers\CouponCodeRelationManager;
use App\Models\Discount;
use DateTime;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Laravel\Cashier\Coupon;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $modelLabel = 'Discount';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Discount::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'percentage') {
                            return "{$state}%";
                        }

                        return '$'.number_format($state / 100, 2);
                    }),

                Tables\Columns\TextColumn::make('code')
                    ->label('Promo Codes')
                    ->getStateUsing(function ($record) {
                        return $record->promoCodes->count();
                    }),

                Tables\Columns\TextColumn::make('redemption')
                    ->label('Redemptions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Discount')
                    ->color('primary')
                    ->slideOver(),
            ])
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->iconSize('lg'),

                Tables\Actions\EditAction::make()
                    ->label('')
                    ->iconSize('lg')
                    ->color('gray')
                    ->slideOver(),

                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->iconSize('lg')
                    ->successNotificationTitle('Discount deleted successfully')
                    ->requiresConfirmation()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Discount Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('amount')
                            ->label('Discount Amount')
                            ->formatStateUsing(function ($state, $record) {
                                if ($record->type === 'percentage') {
                                    return "{$state}%";
                                }

                                return '$'.number_format($state / 100, 2);
                            }),

                        TextEntry::make('valid_until')
                            ->label('Valid Until')
                            ->formatStateUsing(function ($state, $record) {
                                if ($state instanceof DateTime) {
                                    return $state->format('Y-m-d H:i:s');
                                }

                                return $state;
                            }),

                        // TextEntry::make('coupon_id')
                        //     ->label('ID')
                        //     ->badge(
                        //         fn($state) => $state,
                        //     )
                        //     ->color(
                        //         fn($state) => $state ? 'warning' : 'info',
                        //     ),

                        TextEntry::make('max_redemptions')
                            ->label('Max Number of Redemptions'),

                        TextEntry::make('duration_in_months')
                            ->label('Durations (Months)'),

                        TextEntry::make('maximum_recurring_intervals')
                            ->label('Max Recurrences'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'prose prose-invert'])
                            ->html(),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->formatStateUsing(function ($state, $record) {
                                if ($state instanceof DateTime) {
                                    return $state->format('Y-m-d H:i:s');
                                }

                                return $state;
                            }),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->formatStateUsing(function ($state, $record) {
                                if ($state instanceof DateTime) {
                                    return $state->format('Y-m-d H:i:s');
                                }

                                return $state;
                            }),

                    ]),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            CouponCodeRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscount::route('/'),
            'view' => Pages\ViewDiscount::route('/{record}'),
        ];
    }
}
