<?php

namespace App\Providers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // كل الصور اللي بتترفع من الداش بورد بتتحفظ في مساحة عامة عشان الموقع يعرضها
        FileUpload::configureUsing(fn (FileUpload $upload) => $upload
            ->disk('public')
            ->visibility('public')
            ->maxSize(5120)
            ->imageResizeMode('contain')
            ->imageResizeUpscale(false)
            ->imageResizeTargetWidth('1920'));

        RichEditor::configureUsing(fn (RichEditor $editor) => $editor
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public'));

        ImageColumn::configureUsing(fn (ImageColumn $column) => $column->disk('public'));
    }
}
