<?php

namespace App\Filament\Resources\Products\Tables;

use App\Support\Frontend;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')->label('')->square(),
                TextColumn::make('name')->label('الاسم')->searchable(['name', 'sku'])->sortable()
                    ->description(fn ($record) => $record->categories->pluck('name')->join('، '))
                    ->limit(40),
                TextColumn::make('price')->label('السعر')->money('EGP', locale: 'en')->sortable()
                    ->description(fn ($record) => $record->hasDiscount() ? 'بعد الخصم: '.number_format($record->sale_price).' ج.م' : null),
                ToggleColumn::make('is_active')->label('ظاهر'),
                ToggleColumn::make('in_stock')->label('متاح'),
                ToggleColumn::make('is_featured')->label('مميز'),
                TextColumn::make('views')->label('المشاهدات')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('آخر تعديل')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('categories'))
            ->filters([
                SelectFilter::make('categories')->label('التصنيف')->relationship('categories', 'name')->preload(),
                TernaryFilter::make('is_active')->label('ظاهر'),
                TernaryFilter::make('in_stock')->label('متاح'),
                TernaryFilter::make('is_featured')->label('مميز'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => Frontend::url('products/'.$record->slug), true),
                ReplicateAction::make()
                    ->label('نسخ')
                    ->excludeAttributes(['slug', 'views'])
                    ->beforeReplicaSaved(fn ($replica) => $replica->name .= ' (نسخة)')
                    ->after(fn ($record, $replica) => $replica->categories()->sync($record->categories->pluck('id'))),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('hide')
                        ->label('إخفاء')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    BulkAction::make('show')
                        ->label('إظهار')
                        ->icon('heroicon-o-eye')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
