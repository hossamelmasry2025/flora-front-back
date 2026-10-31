<?php

namespace App\Filament\Resources\HomeSections\Schemas;

use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Slides\SlideResource;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class HomeSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        $is = fn (string ...$types) => fn ($get) => in_array($get('type'), $types, true);

        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('نوع القسم')
                        ->icon('heroicon-o-squares-plus')
                        ->schema([
                            ToggleButtons::make('type')
                                ->hiddenLabel()
                                ->options(HomeSection::TYPES)
                                ->icons(HomeSection::ICONS)
                                ->required()
                                ->live()
                                ->columns(['default' => 2, 'lg' => 5])
                                ->disabledOn('edit'),
                        ])
                        ->hiddenOn('edit'),

                    Section::make('العنوان')
                        ->icon('heroicon-o-pencil-square')
                        ->description('بيظهر فوق القسم. سيبه فاضي لو مش عايز عنوان.')
                        ->visible(fn ($get) => filled($get('type')) && $get('type') !== 'slider')
                        ->schema([
                            Tabs::make()->contained(false)->tabs([
                                Tab::make('العربي')->columns(2)->schema([
                                    TextInput::make('title')->label('العنوان'),
                                    TextInput::make('subtitle')->label('سطر تحت العنوان'),
                                ]),
                                Tab::make('English')->columns(2)->schema([
                                    TextInput::make('title_en')->label('Title'),
                                    TextInput::make('subtitle_en')->label('Subtitle'),
                                ]),
                            ]),
                        ]),

                    // ===== السلايدر =====
                    Callout::make('صور السلايدر ليها صفحة لوحدها')
                        ->description('من "السلايدر" في القايمة: ترفع صور كتير مرة واحدة، وتكتب على كل صورة، وتتحكم في السرعة والارتفاع.')
                        ->info()
                        ->actions([Action::make('slides')->label('افتح السلايدر')->url(SlideResource::getUrl())->button()])
                        ->visible($is('slider')),

                    // ===== البانرات =====
                    Callout::make('هنا بيظهر كل بانر مكانه "الصفحة الرئيسية"')
                        ->description('من صفحة "البانرات": ضيف بانر واختار مكانه "الصفحة الرئيسية" وهيظهر هنا على طول بالترتيب. البانرات "نص العرض" و"تلت العرض" بتظهر جنب بعض.')
                        ->info()
                        ->actions([Action::make('banners')->label('افتح البانرات')->url(BannerResource::getUrl('index', ['tab' => 'home']))->button()])
                        ->visible($is('banners')),

                    // ===== التصنيفات =====
                    Section::make('التصنيفات')
                        ->icon('heroicon-o-squares-2x2')
                        ->visible($is('categories'))
                        ->schema([
                            Select::make('settings.category_ids')->label('التصنيفات المعروضة')->multiple()
                                ->options(fn () => Category::query()->orderBy('sort_order')->pluck('name', 'id'))
                                ->helperText('سيبها فاضية عشان يظهر كل التصنيفات المعلّم عليها "تظهر في الرئيسية".'),
                            ToggleButtons::make('settings.style')->label('شكل العرض')->inline()->grouped()->default('grid')->options([
                                'grid' => 'كروت بالصور', 'circles' => 'دواير', 'carousel' => 'سلايدر أفقي',
                            ]),
                        ]),

                    // ===== المنتجات =====
                    Section::make('المنتجات')
                        ->icon('heroicon-o-gift')
                        ->visible($is('products'))
                        ->schema([
                            Select::make('settings.source')->label('أنهي منتجات؟')->default('featured')->required()->live()->native(false)->options([
                                'featured' => 'المنتجات المميزة', 'latest' => 'أحدث المنتجات', 'sale' => 'العروض والخصومات',
                                'popular' => 'الأكثر مشاهدة', 'category' => 'من تصنيف معين', 'manual' => 'أختارهم بنفسي',
                            ]),
                            Select::make('settings.category_id')->label('التصنيف')
                                ->options(fn () => Category::query()->pluck('name', 'id'))
                                ->visible(fn ($get) => $get('settings.source') === 'category'),
                            Select::make('settings.product_ids')->label('المنتجات')->multiple()->searchable()
                                ->getSearchResultsUsing(fn (string $search) => Product::where('name', 'like', "%{$search}%")->limit(30)->pluck('name', 'id'))
                                ->getOptionLabelsUsing(fn (array $values) => Product::whereIn('id', $values)->pluck('name', 'id'))
                                ->visible(fn ($get) => $get('settings.source') === 'manual'),
                            Grid::make(3)->schema([
                                ToggleButtons::make('settings.style')->label('شكل العرض')->inline()->grouped()->default('carousel')
                                    ->options(['carousel' => 'سلايدر', 'grid' => 'شبكة']),
                                TextInput::make('settings.limit')->label('العدد')->numeric()->default(8)->minValue(1)->maxValue(24),
                                Toggle::make('settings.show_more_link')->label('زرار "عرض الكل"')->default(true)->inline(false),
                            ]),
                        ]),

                    // ===== المميزات =====
                    Section::make('المميزات')
                        ->icon('heroicon-o-sparkles')
                        ->visible($is('features'))
                        ->schema([
                            Repeater::make('settings.items')->hiddenLabel()->schema([
                                Select::make('icon')->label('الأيقونة')->required()->options([
                                    'truck' => '🚚 توصيل', 'clock' => '⏰ نفس اليوم', 'flower' => '🌸 ورد طبيعي', 'gift' => '🎁 هدية',
                                    'card' => '💌 كارت', 'shield' => '🛡️ ضمان', 'wallet' => '💳 دفع', 'star' => '⭐ جودة',
                                    'heart' => '❤️ حب', 'phone' => '📞 دعم',
                                ]),
                                TextInput::make('title')->label('العنوان')->required(),
                                TextInput::make('text')->label('وصف قصير'),
                                TextInput::make('title_en')->label('Title (English)')->columnStart(2),
                                TextInput::make('text_en')->label('Text (English)'),
                            ])->columns(3)->maxItems(6)->reorderable()->collapsible()->addActionLabel('إضافة ميزة')
                                ->itemLabel(fn (array $state) => $state['title'] ?? null),
                        ]),

                    // ===== دعوة للشراء =====
                    Section::make('الزراير')
                        ->icon('heroicon-o-megaphone')
                        ->visible($is('cta'))
                        ->columns(2)
                        ->schema([
                            TextInput::make('settings.button_text')->label('نص الزرار')->placeholder('اطلب دلوقتي'),
                            TextInput::make('settings.button_text_en')->label('Button (English)'),
                            TextInput::make('settings.link')->label('رابط الزرار')->placeholder('/products')->columnSpanFull(),
                            TextInput::make('settings.secondary_text')->label('زرار تاني (اختياري)')->placeholder('كلمنا واتساب'),
                            TextInput::make('settings.secondary_text_en')->label('Second button (English)'),
                            TextInput::make('settings.secondary_link')->label('رابط الزرار التاني')->placeholder('https://wa.me/20...')->columnSpanFull(),
                        ]),

                    // ===== نص =====
                    Section::make('المحتوى')
                        ->icon('heroicon-o-document-text')
                        ->visible($is('text'))
                        ->schema([
                            Tabs::make()->contained(false)->tabs([
                                Tab::make('العربي')->schema([RichEditor::make('settings.content')->hiddenLabel()]),
                                Tab::make('English')->schema([RichEditor::make('settings.content_en')->hiddenLabel()]),
                            ]),
                        ]),

                    // ===== فيديو =====
                    Section::make('الفيديو')
                        ->icon('heroicon-o-play-circle')
                        ->visible($is('video'))
                        ->schema([
                            TextInput::make('settings.url')->label('رابط الفيديو من YouTube')->url()->placeholder('https://www.youtube.com/watch?v=...'),
                        ]),

                    // ===== آراء العملاء =====
                    Section::make('الآراء')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->visible($is('testimonials'))
                        ->schema([
                            Repeater::make('settings.items')->hiddenLabel()->schema([
                                TextInput::make('name')->label('اسم العميل')->required(),
                                Select::make('rating')->label('التقييم')->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★'])->default(5),
                                Textarea::make('text')->label('الرأي')->required()->rows(2)->columnSpanFull(),
                                Textarea::make('text_en')->label('Review (English)')->rows(2)->columnSpanFull(),
                            ])->columns(2)->reorderable()->collapsible()->addActionLabel('إضافة رأي')
                                ->itemLabel(fn (array $state) => $state['name'] ?? null),
                        ]),

                    // ===== أسئلة شائعة =====
                    Section::make('الأسئلة')
                        ->icon('heroicon-o-question-mark-circle')
                        ->visible($is('faq'))
                        ->schema([
                            Repeater::make('settings.items')->hiddenLabel()->schema([
                                TextInput::make('question')->label('السؤال')->required(),
                                Textarea::make('answer')->label('الإجابة')->required()->rows(2),
                                TextInput::make('question_en')->label('Question (English)'),
                                Textarea::make('answer_en')->label('Answer (English)')->rows(2),
                            ])->reorderable()->collapsible()->addActionLabel('إضافة سؤال')
                                ->itemLabel(fn (array $state) => $state['question'] ?? null),
                        ]),
                ])->columnSpan(2),

                Group::make([
                    Section::make('الظهور')->icon('heroicon-o-eye')->schema([
                        Toggle::make('is_active')->label('ظاهر في الموقع')->default(true),
                    ]),
                    Section::make('شكل القسم')
                        ->icon('heroicon-o-swatch')
                        ->visible(fn ($get) => filled($get('type')) && $get('type') !== 'slider')
                        ->schema([
                            Select::make('bg')->label('الخلفية')->default('none')->native(false)->options([
                                'none' => 'بدون (لون الصفحة)', 'white' => 'أبيض', 'soft' => 'وردي فاتح', 'sand' => 'بيج', 'brand' => 'لون الموقع (غامق)',
                            ]),
                            ToggleButtons::make('spacing')->label('المسافة فوق وتحت')->inline()->grouped()->default('md')->options([
                                'none' => 'بدون', 'sm' => 'صغيرة', 'md' => 'متوسطة', 'lg' => 'كبيرة',
                            ]),
                            ToggleButtons::make('align')->label('العنوان')->inline()->grouped()->default('start')->options([
                                'start' => 'على الجنب', 'center' => 'في النص',
                            ]),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
