<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Filament\Support\VisualFields;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    VisualFields::images('slides', 'عريضة 1920×800 (الكلام بيتكتب فوقها).'),
                    VisualFields::texts(),
                    VisualFields::look(),
                ])->columnSpan(2),
                Group::make([
                    VisualFields::visibility(),
                ])->columnSpan(1),
            ]);
    }
}
