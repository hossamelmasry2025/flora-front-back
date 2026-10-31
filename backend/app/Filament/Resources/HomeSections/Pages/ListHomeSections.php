<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use App\Support\Frontend;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomeSections extends ListRecords
{
    protected static string $resource = HomeSectionResource::class;

    public function getSubheading(): ?string
    {
        return 'دي أقسام الصفحة الرئيسية بنفس ترتيبها في الموقع من فوق لتحت. اضغط على أي قسم عشان تعدّله، أو "ترتيب بالسحب" عشان تغيّر مكانه.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('شوف الصفحة')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(Frontend::url(), true),
            CreateAction::make()->label('إضافة قسم')->icon('heroicon-o-plus'),
        ];
    }
}
