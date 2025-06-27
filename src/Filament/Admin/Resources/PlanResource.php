<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $modelLabel = 'Prices';

    protected static ?string $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema(Plan::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Plans are the different tiers of your product that you offer to your customers.')
            ->description('For example: if you have Starter, Pro and Premium products, you would create a monthly and yearly plans for each of those to offer them in different intervals.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('interval')
                    ->label('Interval')
                    ->getStateUsing(function ($record) {
                        return $record->interval_count.' '.str($record->interval_period)->plural($record->interval_count);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('has_trial')
                    ->label('Trial')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Plan')
                    ->url(fn () => static::getUrl('create'))
                    ->slideOver(),
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
