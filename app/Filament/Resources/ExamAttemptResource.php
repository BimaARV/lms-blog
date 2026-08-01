<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamAttemptResource\Pages;
use App\Models\ExamAttempt;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Ujian / LMS';

    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Attempt';

    public static function form(Form $form): Form
    {
        // Read-only resource, mostly for review
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['id'] ?? '—') : $state)
                    ->limit(30),
                Tables\Columns\TextColumn::make('score')->label('Skor'),
                Tables\Columns\TextColumn::make('max_score')->label('Max'),
                Tables\Columns\TextColumn::make('percent')
                    ->label('Persen')
                    ->state(fn (ExamAttempt $record) => $record->percentScore() . '%'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'in_progress', 'info' => 'submitted',
                    'success' => 'graded', 'danger' => 'expired',
                ]),
                Tables\Columns\TextColumn::make('started_at')->dateTime('d M Y H:i')->label('Mulai'),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime('d M Y H:i')->label('Submit'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'in_progress' => 'In Progress', 'submitted' => 'Submitted',
                    'graded' => 'Graded', 'expired' => 'Expired',
                ]),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamAttempts::route('/'),
            'view' => Pages\ViewExamAttempt::route('/{record}'),
        ];
    }
}
