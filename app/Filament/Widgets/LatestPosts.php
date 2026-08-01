<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPosts extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->latest()->limit(5))
            ->heading('5 Postingan Terbaru')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['id'] ?? '—') : $state)
                    ->limit(60),
                Tables\Columns\TextColumn::make('author.name')->label('Author'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'success' => 'published',
                    'warning' => 'draft',
                    'info' => 'scheduled',
                    'danger' => 'archived',
                ]),
                Tables\Columns\TextColumn::make('published_at')->dateTime('d M Y')->label('Publish'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Post $record) => PostResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square'),
            ]);
    }
}
