<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $modelLabel = 'Announcement';

    protected static ?string $navigationGroup = 'Announcements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->helperText('The title of the announcement (for internal use only).'),

                        RichEditor::make('content')
                            ->required()
                            ->label('Content')
                            ->columnSpanFull()
                            ->helperText('The content of the announcement.'),

                        DateTimePicker::make('starts_at')
                            ->label('Starts')
                            ->date()
                            ->default(fn () => now()->format('Y-m-d'))
                            ->helperText('The date and time the announcement will start displaying.'),

                        DateTimePicker::make('ends_at')
                            ->label('Ends')
                            ->date()
                            ->default(fn () => now()->format('Y-m-d'))
                            ->helperText('The date and time the announcement will stop displaying.'),

                        Toggle::make('is_active')
                            ->required()
                            ->label('Is Active')
                            ->default(true),

                        Toggle::make('is_dismissable')
                            ->required()
                            ->label('Is Dismissable')
                            ->default(true)
                            ->helperText('If enabled, users will be able to dismiss the announcement.'),

                        Toggle::make('show_on_front_end')
                            ->required()
                            ->label('Show on Frontend')
                            ->default(true)
                            ->helperText('If enabled, the announcement will be displayed on the frontend website.'),

                        Toggle::make('show_on_user_dashboard')
                            ->required()
                            ->label('Show on User Dashboard')
                            ->default(true)
                            ->helperText('If enabled, the announcement will be displayed on the user dashboard.'),

                        Toggle::make('show_for_customers')
                            ->required()
                            ->label('Show for Customers')
                            ->default(true)
                            ->helperText('If enabled, the announcement will be displayed for customers (users who either bought a product or subscribed to a plan).'),

                    ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
