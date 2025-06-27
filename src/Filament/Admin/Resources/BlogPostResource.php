<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $modelLabel = 'Blog Post';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([

                        Forms\Components\Section::make()
                            ->schema([

                                TextInput::make('title')
                                    ->label('Title')
                                    ->required(),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->helperText('A short description of the post (will be used in meta tags).'),

                                RichEditor::make('content')
                                    ->label('Body')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'link',
                                        'bulletList',
                                        'numberedList',
                                        'blockquote',
                                        'codeBlock',
                                        'image',
                                        'table',
                                        'undo',
                                        'redo',
                                    ])
                                    ->placeholder('Write the content of the blog post here...'),
                            ]),

                        Forms\Components\Section::make()
                            ->schema([
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->helperText('Will be used in the URL of the post. Leave empty to generate slug automatically from title.'),

                                Select::make('blog_category_id')
                                    ->label('Blog Post Category')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('user_id')
                                    ->label('Author')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                FileUpload::make('image')
                                    ->image()
                                    ->label('Image')
                                    ->required()
                                    ->directory('blog-posts')
                                    ->acceptedFileTypes(['image/*'])
                                    ->maxSize(1024)
                                    ->preserveFilenames(),

                                Toggle::make('is_published')
                                    ->required()
                                    ->label('Is Published')
                                    ->default(false),

                                DateTimePicker::make('published_at')
                                    ->label('Published At')
                                    ->date(),
                            ])
                            ->label('Publisher Information'),

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

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Is Published')
                    ->boolean(),

            ])->filters([
                //
            ])->headerActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
