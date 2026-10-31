<?php

namespace App\Filament\Resources\HomeSections\Tables;

use App\Models\HomeSection;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HomeSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn ($action, bool $isReordering) => $action
                ->button()
                ->label($isReordering ? 'خلصت الترتيب' : 'ترتيب بالسحب')
                ->icon('heroicon-o-arrows-up-down'))
            ->paginated(false)
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->rowIndex()
                    ->color('gray'),
                TextColumn::make('type')
                    ->label('القسم')
                    ->formatStateUsing(fn (string $state) => HomeSection::TYPES[$state] ?? $state)
                    ->icon(fn (string $state) => HomeSection::ICONS[$state] ?? null)
                    ->iconColor('primary')
                    ->weight(FontWeight::Bold)
                    ->description(fn (HomeSection $record) => $record->title),
                ToggleColumn::make('is_active')->label('ظاهر'),
            ])
            ->recordUrl(fn (HomeSection $record) => route('filament.admin.resources.home-sections.edit', $record))
            ->recordActions([
                EditAction::make()->label('تعديل')->button()->outlined(),
                ReplicateAction::make()->iconButton()->tooltip('نسخة')
                    ->beforeReplicaSaved(fn (HomeSection $replica) => $replica->sort_order = HomeSection::max('sort_order') + 1),
                DeleteAction::make()->iconButton()->tooltip('حذف'),
            ]);
    }
}
