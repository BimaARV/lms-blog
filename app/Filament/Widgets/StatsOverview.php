<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('Semua artikel')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('success'),
            Stat::make('Published', Post::where('status', 'published')->count())
                ->description('Postingan tayang')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('Total Users', User::count())
                ->description('User terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make('Total Exams', Exam::count())
                ->description('Ujian aktif: ' . Exam::where('status', 'published')->count())
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
            Stat::make('Attempts', ExamAttempt::count())
                ->description('Sudah dinilai: ' . ExamAttempt::where('status', 'graded')->count())
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('success'),
        ];
    }
}
