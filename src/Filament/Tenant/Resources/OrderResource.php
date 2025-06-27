<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'refunded' => 'gray',
                        'disputed' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'pending' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => Str::ucfirst($state)),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Price')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state / 100)
                    ->money('USD'),

                Tables\Columns\TextColumn::make('total_discount')
                    ->label('Discount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state / 100)
                    ->money('USD'),

                Tables\Columns\TextColumn::make('total_after_discount')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state / 100)
                    ->money('USD'),

                Tables\Columns\TextColumn::make('payment_provider')
                    ->label('Payment Provider')
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
