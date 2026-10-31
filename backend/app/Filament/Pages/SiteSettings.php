<?php

namespace App\Filament\Pages;

use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SiteSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    protected static ?string $slug = 'settings';

    protected function defaults(): array
    {
        return [
            'site_name' => 'فلورا', 'tagline' => null,
            'announcement' => null, 'footer_about' => null, 'currency' => 'ج.م',
            'phone' => null, 'whatsapp' => null, 'email' => null, 'address' => null, 'city' => 'القاهرة',
            'working_hours' => null, 'social' => [],
            'meta_title' => null, 'meta_description' => null, 'meta_keywords' => null, 'og_image' => null,
            'google_verification' => null, 'google_analytics_id' => null, 'facebook_pixel_id' => null,
            'cod_enabled' => true, 'transfer_enabled' => true, 'transfer_instructions' => null, 'transfer_accounts' => [],
            'time_slots' => [], 'min_order' => 0, 'card_max_length' => 300, 'same_day_cutoff' => null,
            // النسخة الإنجليزي
            'site_name_en' => 'Flora', 'tagline_en' => null, 'announcement_en' => null, 'footer_about_en' => null,
            'currency_en' => 'EGP', 'address_en' => null, 'city_en' => 'Cairo', 'working_hours_en' => null,
            'meta_title_en' => null, 'meta_description_en' => null, 'meta_keywords_en' => null, 'transfer_instructions_en' => null,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->persistTabInQueryString()->tabs([
                Tab::make('عام')->icon('heroicon-o-home')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('site_name')->label('اسم المتجر')->required(),
                        TextInput::make('tagline')->label('الشعار النصي (Tagline)')->placeholder('ورد طبيعي يوصل لحبايبك في نفس اليوم'),
                    ]),
                    TextInput::make('announcement')->label('شريط الإعلان أعلى الموقع')
                        ->placeholder('🚚 توصيل مجاني للطلبات فوق 1000 ج.م'),
                    Textarea::make('footer_about')->label('نبذة في الفوتر')->rows(3),
                    TextInput::make('currency')->label('العملة')->required()->maxLength(10),
                ]),

                Tab::make('English')->icon('heroicon-o-globe-alt')->schema([
                    Section::make('النسخة الإنجليزي من الموقع')
                        ->description('أي خانة فاضية هنا بيظهر مكانها الكلام العربي.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('site_name_en')->label('Store name'),
                            TextInput::make('tagline_en')->label('Tagline'),
                            TextInput::make('announcement_en')->label('Announcement bar')->columnSpanFull(),
                            Textarea::make('footer_about_en')->label('Footer about')->rows(3)->columnSpanFull(),
                            TextInput::make('currency_en')->label('Currency'),
                            TextInput::make('city_en')->label('City'),
                            Textarea::make('address_en')->label('Address')->rows(2),
                            TextInput::make('working_hours_en')->label('Working hours'),
                            TextInput::make('meta_title_en')->label('Home page Google title')->maxLength(70)->columnSpanFull(),
                            Textarea::make('meta_description_en')->label('Site description (Google)')->rows(3)->maxLength(170)->columnSpanFull(),
                            TextInput::make('meta_keywords_en')->label('Keywords')->columnSpanFull(),
                            Textarea::make('transfer_instructions_en')->label('Transfer instructions')->rows(2)->columnSpanFull(),
                        ]),
                ]),

                Tab::make('التواصل')->icon('heroicon-o-phone')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('phone')->label('رقم التليفون')->tel(),
                        TextInput::make('whatsapp')->label('رقم الواتساب')->tel()->helperText('بالصيغة الدولية: 201xxxxxxxxx'),
                        TextInput::make('email')->label('الإيميل')->email(),
                        TextInput::make('city')->label('المدينة'),
                    ]),
                    Textarea::make('address')->label('العنوان')->rows(2),
                    TextInput::make('working_hours')->label('مواعيد العمل')->placeholder('يومياً من 10 صباحاً حتى 12 منتصف الليل'),
                    Repeater::make('social')->label('السوشيال ميديا')->schema([
                        Select::make('platform')->label('المنصة')->required()->options([
                            'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok',
                            'x' => 'X (Twitter)', 'youtube' => 'YouTube', 'snapchat' => 'Snapchat', 'pinterest' => 'Pinterest',
                        ]),
                        TextInput::make('url')->label('الرابط')->url()->required(),
                    ])->columns(2)->defaultItems(0)->addActionLabel('إضافة حساب'),
                ]),

                Tab::make('SEO ومحركات البحث')->icon('heroicon-o-magnifying-glass')->schema([
                    TextInput::make('meta_title')->label('عنوان الصفحة الرئيسية في جوجل')->maxLength(70)
                        ->placeholder('فلورا | محل ورد أونلاين - توصيل بوكيهات ورد في القاهرة'),
                    Textarea::make('meta_description')->label('وصف الموقع في جوجل')->rows(3)->maxLength(170),
                    TextInput::make('meta_keywords')->label('كلمات مفتاحية (اختياري)')
                        ->helperText('افصل بينهم بفاصلة. جوجل مش بيعتمد عليها كتير، الأهم المحتوى.'),
                    FileUpload::make('og_image')->label('صورة المشاركة على السوشيال (1200×630)')->image()->directory('settings'),
                    Section::make('ربط أدوات جوجل وفيسبوك')->schema([
                        TextInput::make('google_verification')->label('كود تأكيد Google Search Console')
                            ->helperText('من Search Console ← اختار "HTML tag" وانسخ قيمة content بس.'),
                        TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID'),
                    ])->columns(1),
                ]),

                Tab::make('الدفع')->icon('heroicon-o-banknotes')->schema([
                    Toggle::make('cod_enabled')->label('تفعيل الدفع عند الاستلام'),
                    Toggle::make('transfer_enabled')->label('تفعيل الدفع بالتحويل (إنستاباي / محافظ)')->live(),
                    Repeater::make('transfer_accounts')->label('حسابات التحويل')
                        ->visible(fn ($get) => $get('transfer_enabled'))
                        ->schema([
                            TextInput::make('name')->label('الطريقة')->required()->placeholder('InstaPay / فودافون كاش'),
                            TextInput::make('name_en')->label('Method (English)')->placeholder('Vodafone Cash'),
                            TextInput::make('number')->label('الرقم / العنوان')->required(),
                            TextInput::make('holder')->label('باسم (اختياري)'),
                        ])->columns(4)->defaultItems(0)->addActionLabel('إضافة حساب'),
                    Textarea::make('transfer_instructions')->label('تعليمات التحويل للعميل')
                        ->visible(fn ($get) => $get('transfer_enabled'))
                        ->rows(3)
                        ->placeholder('حوّل المبلغ على أي رقم من دول وارفع سكرين شوت التحويل، وهنأكد طلبك فوراً.'),
                ]),

                Tab::make('الطلب والتوصيل')->icon('heroicon-o-truck')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('min_order')->label('الحد الأدنى للطلب')->numeric()->suffix('ج.م')->helperText('0 = بدون حد أدنى'),
                        TextInput::make('card_max_length')->label('أقصى عدد حروف للكارت')->numeric()->minValue(50),
                        TimePicker::make('same_day_cutoff')->label('آخر ميعاد لطلبات نفس اليوم')->seconds(false)
                            ->helperText('بعد الساعة دي، أقرب توصيل يبقى بكره.'),
                    ]),
                    Repeater::make('time_slots')->label('مواعيد التوصيل المتاحة')
                        ->schema([
                            TextInput::make('label')->label('الميعاد')->required()->placeholder('من 12 ظهراً إلى 4 عصراً'),
                            TextInput::make('label_en')->label('English')->placeholder('12 PM – 4 PM'),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('إضافة ميعاد')
                        ->helperText('لو فاضية، العميل مش هيختار وقت.'),
                ]),
            ]),
        ]);
    }
}
