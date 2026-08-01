<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamAttemptResource;
use App\Models\ExamAttempt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAttempts extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ExamAttempt::query()->with(['user', 'exam'])->latest()->limit(5))
            ->heading('5 Attempt Terbaru')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['id'] ?? '—') : $state)
                    ->limit(40),
                Tables\Columns\TextColumn::make('score')->label('Skor')->suffix(' / ' . '%'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'in_progress',
                    'info' => 'submitted',
                    'success' => 'graded',
                    'danger' => 'expired',
                ]),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime('d M Y H:i')->label('Submit'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (ExamAttempt $record) => ExamAttemptResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
