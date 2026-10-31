<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Models\Banner;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    public function getSubheading(): ?string
    {
        return 'كل بانر بتختار هو يظهر فين: الرئيسية، فوق منتجات تصنيف، صفحة المنتج، السلة... واسحب عشان ترتب.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('بانر جديد')->icon('heroicon-o-plus')
                // البانر الجديد بياخد مكان التاب المفتوح
                ->url(fn () => BannerResource::getUrl('create', array_filter(['placement' => $this->activeTab !== 'all' ? $this->activeTab : null]))),
        ];
    }

    public function getTabs(): array
    {
        $counts = Banner::query()->selectRaw('placement, count(*) as c')->groupBy('placement')->pluck('c', 'placement');
        $tabs = ['all' => Tab::make('الكل')];

        foreach (Banner::PLACEMENTS as $key => $label) {
            $tabs[$key] = Tab::make(str($label)->before(' (')->toString())
                ->badge($counts[$key] ?? null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('placement', $key));
        }

        return $tabs;
    }
}
