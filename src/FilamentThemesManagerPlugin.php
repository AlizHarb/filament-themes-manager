<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentThemesManagerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-themes-manager';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                \Alizharb\FilamentThemesManager\Pages\ThemeManager::class,
            ]);

        if (config('filament-themes-manager.widgets.enabled', true)) {
            $panel->widgets(
                config('filament-themes-manager.widgets.widgets', [])
            );
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
