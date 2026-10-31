<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('اسم المنطقة')->required()->placeholder('مثلاً: مدينة نصر'),
                TextInput::make('name_en')->label('Area name (English)')->placeholder('Nasr City'),
                TextInput::make('fee')->label('رسوم التوصيل')->required()->numeric()->minValue(0)->default(0)->suffix('ج.م'),
                Toggle::make('is_active')->label('متاحة')->default(true),
                TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            ]);
    }
}
