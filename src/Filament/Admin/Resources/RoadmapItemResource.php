<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoadmapItemResource\Pages;
use App\Models\RoadmapItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RoadmapItemResource extends Resource
{
    protected static ?string $model = RoadmapItem::class;

    protected static ?string $modelLabel = 'Roadmap Item';

    protected static ?string $navigationGroup = 'Roadmap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([

                        TextInput::make('title')
                            ->label('Title')
                            ->required(),

                        TextInput::make('slug')
                            ->label('Slug'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(10),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'planned' => '🙏 Pending Approval',
                                'approved' => '👍 Approved',
                                'in_progress' => '⏳ In Progress',
                                'completed' => '✅ Completed',
                                'cancelled' => '🛑 Cancelled',
                                'declined' => '👎 Declined',
                            ])
                            ->default('planned')
                            ->required(),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'feature' => '🏅 Feature',
                                'bug' => '🕷️ Bug',
                            ])
                            ->default('feature')
                            ->required(),

                        TextInput::make('upvotes')
                            ->label('Up Votes')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->default(fn () => Auth::user()->id),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => '🙏 Pending Approval',
                        'approved' => '👍 Approved',
                        'in_progress' => '⏳ In Progress',
                        'completed' => '✅ Completed',
                        'cancelled' => '🛑 Cancelled',
                        'declined' => '👎 Declined',
                    ]),

                Tables\Columns\SelectColumn::make('type')
                    ->label('Type')
                    ->options([
                        'feature' => '🏅 Feature',
                        'bug' => '🕷️ Bug',
                    ]),

                Tables\Columns\TextColumn::make('upvotes')
                    ->label('Up Votes')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),

            ])->filters([
                //
            ])->headerActions([
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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'approved')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoadmapItems::route('/'),
            'create' => Pages\CreateRoadmapItem::route('/create'),
            'edit' => Pages\EditRoadmapItem::route('/{record}/edit'),
        ];
    }
}
