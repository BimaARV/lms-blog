<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Konten')
                ->schema([
                    Forms\Components\Tabs::make('Translations')->tabs([
                        Forms\Components\Tabs\Tab::make('Indonesia')
                            ->schema([
                                Forms\Components\TextInput::make('title.id')->label('Judul (ID)')->required()->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if (!$get('slug.id')) {
                                            $set('slug.id', Str::slug($state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug.id')->label('Slug (ID)')->required(),
                                Forms\Components\Textarea::make('excerpt.id')->label('Ringkasan (ID)')->rows(2),
                                Forms\Components\RichEditor::make('body.id')->label('Isi (ID)')->required()->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('title.en')->label('Title (EN)')->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if (!$get('slug.en')) {
                                            $set('slug.en', Str::slug($state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug.en')->label('Slug (EN)'),
                                Forms\Components\Textarea::make('excerpt.en')->label('Excerpt (EN)')->rows(2),
                                Forms\Components\RichEditor::make('body.en')->label('Body (EN)')->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Metadata')
                ->schema([
                    Forms\Components\Select::make('category_id')->label('Kategori')
                        ->relationship('category', 'name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => is_array($record->name) ? ($record->name[app()->getLocale()] ?? $record->name['id'] ?? '—') : $record->name)
                        ->searchable()->preload(),
                    Forms\Components\Select::make('user_id')->label('Author')
                        ->relationship('author', 'name')->required()->searchable()->preload()
                        ->default(auth()->id()),
                    Forms\Components\Select::make('status')->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'scheduled' => 'Scheduled',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])->required()->default('draft'),
                    Forms\Components\DateTimePicker::make('published_at')->label('Publish At'),
                    Forms\Components\FileUpload::make('featured_image')->label('Featured Image')
                        ->image()->disk('public')->directory('posts'),
                    Forms\Components\Toggle::make('allow_comments')->label('Izinkan Komentar')->default(true),
                ])->columns(2),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('meta_title.id')->label('Meta Title (ID)'),
                    Forms\Components\TextInput::make('meta_title.en')->label('Meta Title (EN)'),
                    Forms\Components\Textarea::make('meta_description.id')->label('Meta Description (ID)')->rows(2),
                    Forms\Components\Textarea::make('meta_description.en')->label('Meta Description (EN)')->rows(2),
                ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')->label('Image')->square()->size(40),
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['id'] ?? '—') : $state)
                    ->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('author.name')->label('Author')->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['id'] ?? '—') : $state),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'success' => 'published',
                    'warning' => 'draft',
                    'info' => 'scheduled',
                    'danger' => 'archived',
                ]),
                Tables\Columns\TextColumn::make('views_count')->label('Views')->sortable(),
                Tables\Columns\TextColumn::make('published_at')->label('Publish')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'scheduled' => 'Scheduled',
                    'published' => 'Published', 'archived' => 'Archived',
                ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'id'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
