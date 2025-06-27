<?php

namespace App\Filament\Admin\Resources\DiscountResource\RelationManagers;

use App\Models\Discount;
use App\Models\PromoCode;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CouponCodeRelationManager extends RelationManager
{
    protected static string $relationship = 'promoCodes';

    protected static bool $softDeletes = true;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema(Discount::getForm(
            $this->getOwnerRecord()->discount_id,
        ));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->code;
                    }),

                Tables\Columns\TextColumn::make('redemption_count')
                    ->label('Redemptions')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $redemptions = $record->times_redeemed ?? 0;
                        $max = $record->max_redemptions;

                        if (is_null($max)) {
                            $maxText = '<span class="text-gray-500 italic">unlimited</span>';
                        } else {
                            $maxText = $max;
                        }

                        return "{$redemptions} of {$maxText}";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->label('Expires At')
                    ->alignRight()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])

            ->headerActions([
                Tables\Actions\CreateAction::make()->form(PromoCode::getForm(
                    $this->getOwnerRecord()->discount_id,
                ))->label('Create Promo Code'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->iconSize('lg')
                    ->color('gray')
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
