<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Support\Frontend;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable(),
                TextColumn::make('slug')->label('الرابط')->prefix('/pages/')->color('gray'),
                ToggleColumn::make('is_active')->label('منشورة'),
                ToggleColumn::make('show_in_footer')->label('في الفوتر'),
                TextColumn::make('updated_at')->label('آخر تعديل')->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => Frontend::url('pages/'.$record->slug), true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
