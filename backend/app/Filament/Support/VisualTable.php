<?php

namespace App\Filament\Support;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * عرض السلايدر والبانرات ككروت بالصور (بدل جدول) + ترتيب بالسحب.
 */
class VisualTable
{
    public static function configure(Table $table, array $extraColumns = []): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn ($action, bool $isReordering) => $action
                ->button()
                ->label($isReordering ? 'خلصت الترتيب' : 'ترتيب بالسحب')
                ->icon('heroicon-o-arrows-up-down'))
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginated(false)
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->imageHeight(170)
                        ->imageWidth('100%')
                        ->extraImgAttributes(['class' => 'flora-card-image']),
                    TextColumn::make('title')
                        ->placeholder('(صورة من غير كلام)')
                        ->weight(FontWeight::Bold)
                        ->extraAttributes(['class' => 'pt-3']),
                    TextColumn::make('subtitle')->color('gray')->size('sm')->limit(60),
                    ...$extraColumns,
                    Split::make([
                        ToggleColumn::make('is_active')->label('ظاهر')->tooltip('ظاهر في الموقع'),
                        TextColumn::make('schedule')
                            ->state(fn ($record) => match (true) {
                                $record->ends_at && $record->ends_at->isPast() => 'انتهى '.$record->ends_at->format('d/m'),
                                $record->starts_at && $record->starts_at->isFuture() => 'يبدأ '.$record->starts_at->format('d/m'),
                                $record->ends_at !== null => 'لحد '.$record->ends_at->format('d/m'),
                                default => null,
                            })
                            ->badge()
                            ->color(fn ($record) => $record->ends_at?->isPast() ? 'danger' : 'warning')
                            ->icon('heroicon-o-clock'),
                    ])->extraAttributes(['class' => 'pt-2']),
                ])->space(1),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل')->button()->outlined(),
                ReplicateAction::make()->label('نسخ')->iconButton()->tooltip('نسخة من الصورة دي'),
                DeleteAction::make()->iconButton()->tooltip('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
