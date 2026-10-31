<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProducts extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected static ?string $heading = 'الأكثر مبيعاً';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => OrderItem::query()
                ->selectRaw('MIN(id) as id, product_name, MIN(product_image) as product_image, SUM(quantity) as qty, SUM(total) as revenue')
                ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                ->groupBy('product_name')
                ->orderByDesc('qty')
                ->limit(6))
            ->paginated(false)
            ->columns([
                ImageColumn::make('product_image')->label('')->circular()->imageSize(36),
                TextColumn::make('product_name')->label('المنتج')->limit(28),
                TextColumn::make('qty')->label('الكمية')->badge()->color('primary'),
            ])
            ->emptyStateHeading('لسه مفيش مبيعات')
            ->emptyStateIcon('heroicon-o-gift');
    }
}
