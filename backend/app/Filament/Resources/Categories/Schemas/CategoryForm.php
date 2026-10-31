<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\SeoFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('بيانات التصنيف')->icon('heroicon-o-squares-2x2')->schema([
                        SeoFields::languageTabs([
                            Grid::make(2)->schema([
                                SeoFields::name(),
                                SeoFields::slug('/categories/'),
                            ]),
                            Textarea::make('description')
                                ->label('وصف قصير')
                                ->rows(2)
                                ->helperText('بيظهر أعلى صفحة التصنيف.'),
                            RichEditor::make('content')
                                ->label('محتوى أسفل الصفحة (مهم للـ SEO)')
                                ->helperText('اكتب فقرة أو اتنين عن التصنيف بالكلمات اللي الناس بتدور بيها، مثلاً: "ورد عيد ميلاد في القاهرة".'),
                        ], [
                            Grid::make(2)->schema([
                                SeoFields::name('name', 'Name', en: true),
                                SeoFields::slug('/categories/', en: true),
                            ]),
                            Textarea::make('description_en')->label('Short description')->rows(2),
                            RichEditor::make('content_en')->label('SEO content (bottom of page)'),
                        ]),
                        Select::make('parent_id')
                            ->label('تصنيف رئيسي (اختياري)')
                            ->relationship('parent', 'name', fn ($query, $record) => $query->when($record, fn ($q) => $q->whereKeyNot($record->id)))
                            ->searchable()
                            ->preload()
                            ->helperText('لو ده تصنيف فرعي، اختار التصنيف اللي تحته.'),
                    ]),
                    SeoFields::section(),
                ])->columnSpan(2),

                Group::make([
                    Section::make('الصور')->icon('heroicon-o-photo')->schema([
                        FileUpload::make('image')
                            ->label('صورة التصنيف')
                            ->image()
                            ->imageEditor()
                            ->directory('categories'),
                        FileUpload::make('banner_image')
                            ->label('بانر أعلى الصفحة (اختياري)')
                            ->image()
                            ->imageEditor()
                            ->directory('categories'),
                    ]),
                    Section::make('الظهور')->icon('heroicon-o-eye')->schema([
                        Toggle::make('is_active')->label('ظاهر في الموقع')->default(true),
                        Toggle::make('show_on_home')->label('يظهر في الصفحة الرئيسية')->default(true),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),
                ])->columnSpan(1),
            ]);
    }
}
