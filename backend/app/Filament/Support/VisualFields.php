<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * حقول مشتركة بين صورة السلايدر والبانر: الصورة، الكلام، شكل الكلام، وفترة الظهور.
 */
class VisualFields
{
    public static function images(string $directory, string $desktopHint): Section
    {
        return Section::make('الصورة')
            ->icon('heroicon-o-photo')
            ->columns(2)
            ->schema([
                FileUpload::make('image')
                    ->label('صورة الكمبيوتر')
                    ->helperText($desktopHint)
                    ->image()
                    ->imageEditor()
                    ->required()
                    ->directory($directory),
                FileUpload::make('mobile_image')
                    ->label('صورة الموبايل (اختياري)')
                    ->helperText('طولية 800×1000. لو فاضية بتظهر صورة الكمبيوتر.')
                    ->image()
                    ->imageEditor()
                    ->directory($directory),
            ]);
    }

    public static function texts(): Section
    {
        return Section::make('الكلام والزرار')
            ->icon('heroicon-o-pencil-square')
            ->description('كله اختياري — ممكن تسيب الصورة لوحدها من غير كلام.')
            ->schema([
                Tabs::make()->contained(false)->tabs([
                    Tab::make('العربي')->schema([
                        TextInput::make('title')->label('العنوان الكبير'),
                        TextInput::make('subtitle')->label('سطر تحت العنوان'),
                        TextInput::make('button_text')->label('نص الزرار')->placeholder('تسوق الآن'),
                    ]),
                    Tab::make('English')->schema([
                        TextInput::make('title_en')->label('Title'),
                        TextInput::make('subtitle_en')->label('Subtitle'),
                        TextInput::make('button_text_en')->label('Button text')->placeholder('Shop now'),
                    ]),
                ]),
                TextInput::make('link')
                    ->label('لما العميل يضغط يروح فين؟')
                    ->placeholder('/products   أو   /categories/ورد-عيد-ميلاد   أو   https://...')
                    ->helperText('صفحة في الموقع أو رابط خارجي. لو فاضي الصورة مش هتبقى رابط.'),
            ]);
    }

    public static function look(): Section
    {
        return Section::make('شكل الكلام على الصورة')
            ->icon('heroicon-o-swatch')
            ->schema([
                Grid::make(2)->schema([
                    ToggleButtons::make('text_position')
                        ->label('مكان الكلام')
                        ->options(['start' => 'يمين', 'center' => 'في النص', 'end' => 'شمال'])
                        ->icons(['start' => 'heroicon-o-bars-3-bottom-right', 'center' => 'heroicon-o-bars-3', 'end' => 'heroicon-o-bars-3-bottom-left'])
                        ->default('start')
                        ->inline()
                        ->grouped(),
                    ToggleButtons::make('text_color')
                        ->label('لون الكلام')
                        ->options(['light' => 'أبيض', 'dark' => 'غامق'])
                        ->colors(['light' => 'gray', 'dark' => 'gray'])
                        ->default('light')
                        ->inline()
                        ->grouped(),
                ]),
                Slider::make('overlay')
                    ->label('غمقان الصورة (عشان الكلام يبان)')
                    ->helperText('0 = الصورة زي ما هي، 80 = غامقة جداً. لو مفيش كلام خليها 0.')
                    ->range(0, 80)
                    ->step(5)
                    ->default(45)
                    ->tooltips(),
            ]);
    }

    public static function visibility(): Section
    {
        return Section::make('الظهور')
            ->icon('heroicon-o-eye')
            ->schema([
                Toggle::make('is_active')->label('ظاهر في الموقع')->default(true),
                DateTimePicker::make('starts_at')->label('يبدأ يظهر من (اختياري)')->seconds(false)
                    ->helperText('مفيد للعروض: البانر يظهر لوحده في الميعاد ده.'),
                DateTimePicker::make('ends_at')->label('يختفي بعد (اختياري)')->seconds(false)->after('starts_at'),
            ]);
    }
}
