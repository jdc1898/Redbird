<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmailProviderResource\Pages;
use App\Models\EmailProvider;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailProviderResource extends Resource
{
    protected static ?string $model = EmailProvider::class;

    protected static ?string $modelLabel = 'Email Provider';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

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
                        $icon = asset('images/email-providers/mailgun.svg');

                        return '<img src="'.$icon.'" alt="" class="inline w-5 h-5 mr-2" style="margin-right: 5px;">'.e($state);
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
            'index' => Pages\ListEmailProviders::route('/'),
            'create' => Pages\CreateEmailProvider::route('/create'),
            'edit' => Pages\EditEmailProvider::route('/{record}/edit'),
        ];
    }
}
