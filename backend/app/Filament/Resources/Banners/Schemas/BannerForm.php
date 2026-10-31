<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Filament\Support\VisualFields;
use App\Models\Banner;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    VisualFields::images('banners', 'عريضة 1920×600 للعرض الكامل، أو 1000×700 لو نص العرض.'),
                    VisualFields::texts(),
                    VisualFields::look(),
                ])->columnSpan(2),

                Group::make([
                    Section::make('البانر يظهر فين؟')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Select::make('placement')
                                ->hiddenLabel()
                                ->options(Banner::PLACEMENTS)
                                ->default(request('placement', 'home'))
                                ->required()
                                ->native(false)
                                ->live(),
                            Select::make('category_id')
                                ->label('في أنهي تصنيف؟')
                                ->options(fn () => Category::query()->orderBy('sort_order')->pluck('name', 'id'))
                                ->placeholder('كل التصنيفات')
                                ->helperText('سيبها "كل التصنيفات" عشان يظهر في أي تصنيف.')
                                ->visible(fn ($get) => $get('placement') === 'category'),
                        ]),
                    Section::make('المقاس')
                        ->icon('heroicon-o-arrows-pointing-out')
                        ->schema([
                            ToggleButtons::make('size')
                                ->label('العرض')
                                ->helperText('بانرين "نص العرض" ورا بعض بيظهروا جنب بعض.')
                                ->options(Banner::SIZES)
                                ->default('full')
                                ->inline()
                                ->grouped(),
                            ToggleButtons::make('height')
                                ->label('الارتفاع')
                                ->options(Banner::HEIGHTS)
                                ->default('md')
                                ->inline()
                                ->grouped(),
                        ]),
                    VisualFields::visibility(),
                ])->columnSpan(1),
            ]);
    }
}
