<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use App\Support\Frontend;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->brandName(fn () => rescue(fn () => Setting::get('site_name', 'فلورا'), 'فلورا', false).' — لوحة التحكم')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => rescue(fn () => \App\Support\Frontend::media(Setting::get('favicon')), null, false))
            ->spa()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.styles'))
            ->colors([
                'primary' => [
                    50 => '#fdf2f5', 100 => '#fbe4ea', 200 => '#f6c9d5', 300 => '#ee9fb4', 400 => '#e06d8d',
                    500 => '#cc4670', 600 => '#b02f59', 700 => '#922449', 800 => '#7a2140', 900 => '#5c1830', 950 => '#3a0e1e',
                ],
                'gray' => Color::Stone,
            ])
            ->font('Cairo')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('المتجر'),
                NavigationGroup::make('الصفحة الرئيسية'),
                NavigationGroup::make('المحتوى'),
                NavigationGroup::make('الإعدادات'),
            ])
            ->navigationItems([
                NavigationItem::make('فتح الموقع')
                    ->url(fn () => Frontend::url(), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-alt')
                    ->sort(99),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
