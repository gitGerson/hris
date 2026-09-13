<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Services\AuthBackgroundImageService;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Enums\ThemeMode;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Configure the admin panel. UI tweaks are commented inline.
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            // Light only, no theme switcher.
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            // Custom page: appends the app name to the sign in heading.
            ->login(Login::class)
            ->brandName('HRIS')
            ->favicon(asset('image/logo/1.png'))
            // Custom family, so Filament serves it through Bunny, the same host Vite uses.
            ->font('Plus Jakarta Sans')
            // View renders the mark, plus the "HRIS" text outside the auth pages.
            ->brandLogo(fn (): View => view('filament.logo', [
                'height' => $this->logoHeight(),
                'showText' => ! $this->isAuthPage(),
            ]))
            // Height is an inline style, so resolve per request: bigger on auth pages.
            ->brandLogoHeight(fn (): string => $this->logoHeight())
            ->colors([
                'primary' => Color::Rose,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                // Roles sit with Users under Settings; the resource reads nav off the plugin.
                FilamentShieldPlugin::make()
                    ->navigationGroup('Settings')
                    ->navigationSort(3),
                // Profile page in the user menu, with avatar upload and session management.
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: true,
                    )
                    // FILESYSTEM_DISK is local, which is not web accessible, so pin uploads
                    // to the public disk that the storage symlink serves.
                    ->avatarUploadComponent(fn (FileUpload $fileUpload): FileUpload => $fileUpload
                        ->disk('public')
                        ->directory('avatars')
                        ->visibility('public')
                        ->avatar()
                        ->imageEditor())
                    ->enableBrowserSessions(),
                // 35% width needs the padding override in resources/css/filament/admin/theme.css.
                AuthUIEnhancerPlugin::make()
                    ->formPanelPosition('right')
                    ->formPanelWidth('35%')
                    ->emptyPanelBackgroundColor(Color::Rose, '600')
                    // Random image per request; falls back to the color above.
                    ->emptyPanelBackgroundImageUrl(app(AuthBackgroundImageService::class)->randomUrl())
                    ->emptyPanelBackgroundImageOpacity('70%')
                    ->showEmptyPanelOnMobile(false),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Logo height: larger on the auth pages than in the topbar.
     */
    protected function logoHeight(): string
    {
        return $this->isAuthPage() ? '5rem' : '3rem';
    }

    /**
     * Whether the current request is an auth page, such as login.
     */
    protected function isAuthPage(): bool
    {
        return str_contains(request()->route()?->getName() ?? '', '.auth.');
    }
}
