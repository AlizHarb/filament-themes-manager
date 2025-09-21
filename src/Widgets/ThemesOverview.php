<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Widgets;

use Alizharb\FilamentThemesManager\Services\ThemeManagerService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ThemesOverview extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $service = app(ThemeManagerService::class);
        $stats = $service->getThemeStats();

        return [
            Stat::make(__('filament-themes-manager::theme.stats.total_themes'), $stats['total'])
                ->description(__('filament-themes-manager::theme.stats.total_themes_description'))
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('primary'),

            Stat::make(__('filament-themes-manager::theme.stats.active_theme'), $stats['active'])
                ->description(__('filament-themes-manager::theme.stats.active_theme_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('filament-themes-manager::theme.stats.valid_themes'), $stats['valid'])
                ->description(__('filament-themes-manager::theme.stats.valid_themes_description'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info'),

            Stat::make(__('filament-themes-manager::theme.stats.invalid_themes'), $stats['invalid'])
                ->description(__('filament-themes-manager::theme.stats.invalid_themes_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stats['invalid'] > 0 ? 'warning' : 'success'),
        ];
    }
}
