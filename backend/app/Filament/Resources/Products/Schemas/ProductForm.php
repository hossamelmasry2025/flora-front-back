<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\SeoFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('بيانات المنتج')->icon('heroicon-o-gift')->schema([
                        SeoFields::languageTabs([
                            Grid::make(2)->schema([
                                SeoFields::name('name', 'اسم البوكيه / المنتج'),
                                SeoFields::slug('/products/'),
                            ]),
                            Textarea::make('short_description')
                                ->label('وصف مختصر')
                                ->rows(2)
                                ->maxLength(300)
                                ->helperText('سطر أو اتنين بيظهروا جنب السعر.'),
                            RichEditor::make('description')
                                ->label('التفاصيل الكاملة')
                                ->fileAttachmentsDirectory('products/content'),
                        ], [
                            Grid::make(2)->schema([
                                SeoFields::name('name', 'Product name', en: true),
                                SeoFields::slug('/products/', en: true),
                            ]),
                            Textarea::make('short_description_en')->label('Short description')->rows(2)->maxLength(300),
                            RichEditor::make('description_en')->label('Full description')->fileAttachmentsDirectory('products/content'),
                        ]),
                    ]),

                    Section::make('الصور')->icon('heroicon-o-photo')->schema([
                        FileUpload::make('image')
                            ->label('الصورة الرئيسية')
                            ->image()
                            ->imageEditor()
                            ->required()
                            ->directory('products')
                            ->helperText('يفضل صورة مربعة (1200×1200) بخلفية نضيفة.'),
                        FileUpload::make('gallery')
                            ->label('صور إضافية')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->panelLayout('grid')
                            ->directory('products'),
                    ]),

                    Section::make('مواصفات إضافية')
                        ->description('مثلاً: عدد الورود — 24 وردة، نوع التغليف — ورق كوري، المقاس — 50 سم')
                        ->collapsible()
                        ->schema([
                            Repeater::make('details')
                                ->hiddenLabel()
                                ->schema([
                                    TextInput::make('label')->label('البند')->required(),
                                    TextInput::make('value')->label('القيمة')->required(),
                                    TextInput::make('label_en')->label('Label (English)'),
                                    TextInput::make('value_en')->label('Value (English)'),
                                ])
                                ->columns(4)
                                ->reorderable()
                                ->defaultItems(0)
                                ->addActionLabel('إضافة مواصفة'),
                        ]),

                    SeoFields::section(),
                ])->columnSpan(2),

                Group::make([
                    Section::make('السعر')->schema([
                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix('ج.م'),
                        TextInput::make('sale_price')
                            ->label('السعر بعد الخصم (اختياري)')
                            ->numeric()
                            ->minValue(0)
                            ->lt('price')
                            ->suffix('ج.م')
                            ->validationMessages(['lt' => 'لازم يكون أقل من السعر الأصلي.']),
                        TextInput::make('sku')->label('كود المنتج (SKU)'),
                    ]),
                    Section::make('التصنيفات')->schema([
                        Select::make('categories')
                            ->hiddenLabel()
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('ممكن تختار أكتر من تصنيف (مثلاً: عيد ميلاد + رومانسي).'),
                    ]),
                    Section::make('الظهور')->schema([
                        Toggle::make('is_active')->label('ظاهر في الموقع')->default(true),
                        Toggle::make('in_stock')->label('متاح للطلب')->default(true),
                        Toggle::make('is_featured')->label('منتج مميز')->default(false)
                            ->helperText('بيظهر في سكشن "المميز" في الرئيسية.'),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),
                ])->columnSpan(1),
            ]);
    }
}
