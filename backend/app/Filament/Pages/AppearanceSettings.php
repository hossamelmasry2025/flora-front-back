<?php

namespace App\Filament\Pages;

use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * شكل الموقع: الألوان، اللوجو، القائمة (الهيدر)، والفوتر.
 */
class AppearanceSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'الشكل والألوان والقائمة';

    protected static ?string $title = 'شكل الموقع';

    protected static ?string $slug = 'appearance';

    protected function defaults(): array
    {
        return [
            'logo' => null, 'favicon' => null,
            'brand_color' => '#b02f59', 'accent_color' => '#b8935a', 'background_color' => '#fbf7f3',
            'corner_style' => 'rounded',
            'header_menu' => [], 'header_show_search' => true, 'header_show_track' => true, 'header_show_language' => true,
            'header_sticky' => true, 'whatsapp_button' => true,
            'footer_links' => [], 'footer_show_categories' => true, 'footer_show_pages' => true,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->persistTabInQueryString()->tabs([
                Tab::make('الألوان واللوجو')->icon('heroicon-o-swatch')->schema([
                    Section::make('الألوان')->description('غيّر اللون وكل الموقع هيتلوّن بيه: الأزرار، العناوين، الهيدر والفوتر.')->schema([
                        Grid::make(3)->schema([
                            ColorPicker::make('brand_color')->label('اللون الأساسي')->required(),
                            ColorPicker::make('accent_color')->label('لون اللمسات (النجوم والزخارف)')->required(),
                            ColorPicker::make('background_color')->label('لون خلفية الموقع')->required(),
                        ]),
                        Select::make('corner_style')->label('شكل الحواف')->options([
                            'rounded' => 'دايرية ناعمة (الافتراضي)',
                            'soft' => 'دايرية خفيفة',
                            'sharp' => 'حادة / مربعة',
                        ]),
                    ]),
                    Section::make('اللوجو')->columns(2)->schema([
                        FileUpload::make('logo')->label('اللوجو')->image()->directory('settings')
                            ->helperText('PNG أو SVG بخلفية شفافة. لو فاضي بيظهر اسم المتجر.'),
                        FileUpload::make('favicon')->label('أيقونة المتصفح (Favicon)')->image()->directory('settings')
                            ->helperText('مربعة 512×512.'),
                    ]),
                ]),

                Tab::make('القائمة (الهيدر)')->icon('heroicon-o-bars-3')->schema([
                    Repeater::make('header_menu')
                        ->label('روابط القائمة الرئيسية')
                        ->helperText('لو سبتها فاضية، القائمة بتتعمل تلقائي من التصنيفات. رتبها بالسحب.')
                        ->schema([
                            TextInput::make('label')->label('الاسم')->required(),
                            TextInput::make('label_en')->label('Name (English)'),
                            TextInput::make('link')->label('الرابط')->required()->placeholder('/categories/ورد-عيد-ميلاد'),
                            Toggle::make('highlight')->label('مميز (بلون مختلف)')->inline(false),
                        ])
                        ->columns(4)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['label'] ?? null)
                        ->defaultItems(0)
                        ->addActionLabel('إضافة رابط للقائمة'),
                    Grid::make(4)->schema([
                        Toggle::make('header_show_search')->label('خانة البحث'),
                        Toggle::make('header_show_track')->label('زرار تتبع الطلب'),
                        Toggle::make('header_show_language')->label('زرار تغيير اللغة'),
                        Toggle::make('header_sticky')->label('الهيدر ثابت فوق وانت بتنزل'),
                    ]),
                    Toggle::make('whatsapp_button')->label('زرار الواتساب العائم في الركن'),
                ]),

                Tab::make('الفوتر')->icon('heroicon-o-bars-3-bottom-left')->schema([
                    Grid::make(2)->schema([
                        Toggle::make('footer_show_categories')->label('عمود التصنيفات'),
                        Toggle::make('footer_show_pages')->label('عمود الصفحات (من نحن، السياسات...)'),
                    ]),
                    Repeater::make('footer_links')
                        ->label('روابط إضافية في الفوتر')
                        ->schema([
                            TextInput::make('label')->label('الاسم')->required(),
                            TextInput::make('label_en')->label('Name (English)'),
                            TextInput::make('link')->label('الرابط')->required(),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->defaultItems(0)
                        ->addActionLabel('إضافة رابط'),
                ]),
            ]),
        ]);
    }
}
