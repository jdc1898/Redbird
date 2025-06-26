<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\ProductResource\RelationManagers;

use Fullstack\Redbird\Filament\Admin\Forms\PriceForm;
use App\Models\Price;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use App\Http\Controllers\Price\PriceController;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class PriceRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $inverseRelationship = 'product';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema(PriceForm::getForm(
            $this->getOwnerRecord()->id,
        ));
    }

    protected function handleRecordCreation(array $data): Model
    {
        return PriceController::create($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return PriceController::update($record, $data);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Available Prices')
            ->columns([
                TextColumn::make('nickname')
                    ->description(function (Price $record) {

                        $description = '$'.number_format($record->unit_amount / 100, 2);

                        /**
                         * Show the price and the billing interval
                         */
                        if ($record->recurring) {
                            $description .= ' Per '.($record->recurring['interval'] ?? 'period');
                        }

                        if ($record->metadata['package_units'] ?? false) {
                            $units = $record->metadata['package_units'];
                            $label = $record->product?->unit_label ?? 'units';
                            $description .= $units.' '.($units == 1 ? Str::singular($label) : Str::plural($label));
                        }

                        if ($record->billing_scheme === 'tiered') {
                            $description .= ' Starting at $'.number_format($record->tiers[0]['unit_amount'] / 100, 2);
                        }

                        return new HtmlString($description);
                    })
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                // Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->label('Activate')
                    ->requiresConfirmation()
                    ->modalHeading('Activate Price')
                    ->modalDescription('This will make the price available for purchase. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, activate price')
                    ->visible(fn ($record) => !$record->trashed() && !$record->active)
                    ->action(function ($record) {
                        try {
                            $record->active = true;
                            $record->save();

                            Notification::make()
                                ->title('Price activated')
                                ->body('The price is now available for purchase.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error activating price')
                                ->body('There was an error activating the price.')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\DissociateAction::make()
                    ->label('Retire')
                    ->icon('heroicon-o-moon')
                    ->color('danger'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form(PriceForm::getForm($this->getOwnerRecord()->id))
                    ->slideOver()
                    ->label('Create Price'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }
}
