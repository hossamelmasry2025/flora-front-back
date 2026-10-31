<?php

namespace App\Filament\Resources\Slides\Pages;

use App\Filament\Resources\Slides\SlideResource;
use App\Models\Setting;
use App\Models\Slide;
use App\Support\Frontend;
use App\Support\HomeSections;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;

class ListSlides extends ListRecords
{
    protected static string $resource = SlideResource::class;

    public function getSubheading(): ?string
    {
        return 'دي صور السلايدر الكبير أول الصفحة الرئيسية. اسحبها عشان ترتبها، واضغط "تعديل" عشان تكتب عليها كلام وزرار.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkUpload')
                ->label('رفع صور كتير مرة واحدة')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('رفع صور للسلايدر')
                ->modalDescription('اختار كل الصور مرة واحدة (أو اسحبها هنا). كل صورة هتبقى شريحة، وبعدين تقدر تكتب على كل واحدة.')
                ->modalSubmitActionLabel('إضافة للسلايدر')
                ->schema([
                    FileUpload::make('images')
                        ->hiddenLabel()
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->maxFiles(20)
                        ->directory('slides')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $order = (int) Slide::max('sort_order');
                    foreach ($data['images'] as $image) {
                        Slide::create(['image' => $image, 'sort_order' => ++$order, 'overlay' => 30]);
                    }
                    Notification::make()->title('اتضافت '.count($data['images']).' صورة للسلايدر')->success()->send();
                }),

            Action::make('settings')
                ->label('إعدادات السلايدر')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalHeading('إعدادات السلايدر')
                ->modalWidth('2xl')
                ->fillForm(fn () => HomeSections::sliderSettings())
                ->schema([
                    ToggleButtons::make('height')->label('ارتفاع السلايدر')->inline()->grouped()->options([
                        'sm' => 'قصير', 'md' => 'متوسط', 'lg' => 'طويل', 'full' => 'الشاشة كلها',
                    ]),
                    ToggleButtons::make('effect')->label('طريقة التقليب')->inline()->grouped()->options([
                        'fade' => 'تلاشي ناعم', 'slide' => 'تزحلق',
                    ]),
                    Grid::make(2)->schema([
                        Toggle::make('autoplay')->label('يقلب لوحده')->live(),
                        TextInput::make('interval')->label('كل كام ثانية')->numeric()->minValue(2)->maxValue(30)->suffix('ثانية')
                            ->visible(fn ($get) => $get('autoplay')),
                        Toggle::make('arrows')->label('أسهم يمين وشمال'),
                        Toggle::make('dots')->label('النقط تحت'),
                    ]),
                ])
                ->action(function (array $data) {
                    foreach (['height', 'effect', 'autoplay', 'interval', 'arrows', 'dots'] as $key) {
                        Setting::put("slider_{$key}", $data[$key] ?? null);
                    }
                    Notification::make()->title('اتحفظت إعدادات السلايدر')->success()->send();
                }),

            Action::make('preview')
                ->label('شوفه في الموقع')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(Frontend::url(), true),

            CreateAction::make()->label('صورة واحدة')->icon('heroicon-o-plus'),
        ];
    }
}
