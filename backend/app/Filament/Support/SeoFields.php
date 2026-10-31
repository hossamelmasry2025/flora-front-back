<?php

namespace App\Filament\Support;

use App\Support\Slug;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * حقول مشتركة: الاسم + الرابط (slug) + بيانات SEO، بالعربي والإنجليزي.
 */
class SeoFields
{
    public static function name(string $field = 'name', string $label = 'الاسم', bool $en = false): TextInput
    {
        $slugField = $en ? 'slug_en' : 'slug';

        return TextInput::make($en ? "{$field}_en" : $field)
            ->label($label)
            ->required(! $en)
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) use ($slugField, $en) {
                if ($operation === 'create' || blank($get($slugField))) {
                    $set($slugField, $en ? Str::slug((string) $state) : Slug::make((string) $state));
                }
            });
    }

    public static function slug(string $prefix, bool $en = false): TextInput
    {
        return TextInput::make($en ? 'slug_en' : 'slug')
            ->label($en ? 'URL (Slug)' : 'الرابط (Slug)')
            ->prefix($en ? "/en{$prefix}" : $prefix)
            ->maxLength(255)
            ->helperText($en ? null : 'بيتعمل تلقائي من الاسم. الأفضل ما تغيروش بعد النشر عشان ترتيبك في جوجل.')
            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? ($en ? Str::slug($state) : Slug::make($state)) : null);
    }

    /** تابين: العربي (أساسي) و English (اختياري — لو فاضي بيظهر العربي) */
    public static function languageTabs(array $arabic, array $english): Tabs
    {
        return Tabs::make()
            ->contained(false)
            ->tabs([
                Tab::make('العربي')->icon('heroicon-o-language')->schema($arabic),
                Tab::make('English')->icon('heroicon-o-globe-alt')->schema([
                    ...$english,
                ])->badge(fn (Get $get) => self::englishMissing($get, $english) ? '!' : null)
                    ->badgeColor('warning'),
            ]);
    }

    public static function section(): Section
    {
        return Section::make('محركات البحث (SEO)')
            ->description('لو سبتهم فاضيين هيتملوا تلقائياً من الاسم والوصف.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsible()
            ->collapsed()
            ->schema([
                self::languageTabs([
                    TextInput::make('meta_title')
                        ->label('عنوان الصفحة في جوجل')
                        ->maxLength(70)
                        ->helperText('الأفضل أقل من 60 حرف ويحتوي على الكلمة اللي الناس بتدور بيها.'),
                    Textarea::make('meta_description')
                        ->label('وصف الصفحة في جوجل')
                        ->rows(3)
                        ->maxLength(170)
                        ->helperText('من 120 لـ 160 حرف، بيظهر تحت العنوان في نتايج البحث.'),
                ], [
                    TextInput::make('meta_title_en')->label('Google title')->maxLength(70),
                    Textarea::make('meta_description_en')->label('Google description')->rows(3)->maxLength(170),
                ]),
            ]);
    }

    /** علامة تنبيه على تاب English لو الاسم الإنجليزي فاضي */
    private static function englishMissing(Get $get, array $english): bool
    {
        $first = $english[0] ?? null;

        return $first instanceof TextInput && str_ends_with($first->getName(), '_en') && blank($get($first->getName()));
    }
}
