<?php

namespace App\Filament\Resources\Slides\Tables;

use App\Filament\Support\VisualTable;
use Filament\Tables\Table;

class SlidesTable
{
    public static function configure(Table $table): Table
    {
        return VisualTable::configure($table)
            ->emptyStateHeading('لسه مفيش صور في السلايدر')
            ->emptyStateDescription('اضغط "رفع صور كتير مرة واحدة" وارفع كل صور السلايدر مرة واحدة.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
