<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\SeoFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make()->schema([
                        SeoFields::languageTabs([
                            Grid::make(2)->schema([
                                SeoFields::name('title', 'عنوان الصفحة'),
                                SeoFields::slug('/pages/'),
                            ]),
                            TextInput::make('subtitle')->label('سطر تحت العنوان (اختياري)'),
                            RichEditor::make('content')->label('المحتوى')->fileAttachmentsDirectory('pages'),
                        ], [
                            Grid::make(2)->schema([
                                SeoFields::name('title', 'Page title', en: true),
                                SeoFields::slug('/pages/', en: true),
                            ]),
                            TextInput::make('subtitle_en')->label('Subtitle'),
                            RichEditor::make('content_en')->label('Content')->fileAttachmentsDirectory('pages'),
                        ]),
                    ]),
                    SeoFields::section(),
                ])->columnSpan(2),
                Group::make([
                    Section::make('صورة الغلاف')->icon('heroicon-o-photo')->schema([
                        FileUpload::make('image')->hiddenLabel()->image()->imageEditor()->directory('pages')
                            ->helperText('بتظهر خلفية لعنوان الصفحة (1920×700).'),
                    ]),
                    Section::make('الظهور')->icon('heroicon-o-eye')->schema([
                        Toggle::make('is_active')->label('منشورة')->default(true),
                        Toggle::make('show_in_footer')->label('رابط في الفوتر')->default(true),
                        TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),
                ])->columnSpan(1),
            ]);
    }
}
