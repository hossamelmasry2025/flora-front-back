<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    public function getSubheading(): ?string
    {
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'صباح الورد' : 'مساء الورد';

        return "{$greeting} يا ".(auth()->user()?->name ?? '').' 🌸 — دي نظرة سريعة على المتجر النهارده.';
    }

    public function getColumns(): int|array
    {
        return ['md' => 2, 'lg' => 3];
    }
}
