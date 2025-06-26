<?php

namespace Fullstack\Redbird\Filament\Admin\Resources;

use Fullstack\Redbird\Filament\Admin\Resources\PaymentProviderResource\Pages;
use App\Models\PaymentProvider;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentProviderResource extends Resource
{
    protected static ?string $model = PaymentProvider::class;

    protected static ?string $modelLabel = 'Payment Provider';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

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
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $icon = asset('images/payment-providers/stripe.png');

                        return '<img src="'.$icon.'" alt="" class="inline h-6 mr-2" style="margin-right: 5px;">'.e($state);
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListPaymentProviders::route('/'),
            'create' => Pages\CreatePaymentProvider::route('/create'),
            'edit' => Pages\EditPaymentProvider::route('/{record}/edit'),
        ];
    }
}
