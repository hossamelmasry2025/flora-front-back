<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomeSection extends CreateRecord
{
    protected static string $resource = HomeSectionResource::class;

    /** القسم الجديد بيتحط في آخر الصفحة */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) \App\Models\HomeSection::max('sort_order') + 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
