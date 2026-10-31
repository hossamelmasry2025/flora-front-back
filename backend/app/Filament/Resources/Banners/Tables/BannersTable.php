<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Filament\Support\VisualTable;
use App\Models\Banner;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return VisualTable::configure($table, [
            TextColumn::make('placement')
                ->badge()
                ->color('primary')
                ->icon('heroicon-o-map-pin')
                ->formatStateUsing(fn (string $state, Banner $record) => (Banner::PLACEMENTS[$state] ?? $state)
                    .($state === 'category' && $record->category ? ' — '.$record->category->name : '')
                    .' • '.(Banner::SIZES[$record->size] ?? '')),
        ])
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
            ->emptyStateHeading('مفيش بانرات هنا لسه')
            ->emptyStateDescription('اضغط "بانر جديد" وارفع الصورة واختار هيظهر فين.')
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }
}
