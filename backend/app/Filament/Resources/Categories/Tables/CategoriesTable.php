<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Support\Frontend;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('products'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')->label('')->circular(),
                TextColumn::make('name')->label('الاسم')->searchable()->sortable()
                    ->description(fn ($record) => $record->parent?->name),
                TextColumn::make('products_count')->label('المنتجات')->badge()->sortable(),
                ToggleColumn::make('is_active')->label('ظاهر'),
                ToggleColumn::make('show_on_home')->label('في الرئيسية'),
            ])
            ->filters([
                SelectFilter::make('parent_id')->label('التصنيف الرئيسي')->relationship('parent', 'name'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => Frontend::url('categories/'.$record->slug), true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
