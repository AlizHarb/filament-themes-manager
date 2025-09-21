<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\FilamentThemesManagerPlugin;
use Alizharb\FilamentThemesManager\Pages\ThemeManager;
use Alizharb\FilamentThemesManager\Widgets\ThemesOverview;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Filament Theme Manager Integration', function () {
    beforeEach(function () {
        $this->plugin = FilamentThemesManagerPlugin::make();
    });

    it('can register the plugin', function () {
        expect($this->plugin)->toBeInstanceOf(FilamentThemesManagerPlugin::class);
    });

    it('registers the theme manager page', function () {
        $pages = $this->plugin->getPages();

        expect($pages)->toContain(ThemeManager::class);
    });

    it('registers the themes overview widget', function () {
        $widgets = $this->plugin->getWidgets();

        expect($widgets)->toContain(ThemesOverview::class);
    });

    it('can configure navigation', function () {
        $plugin = FilamentThemesManagerPlugin::make()
            ->navigationIcon('heroicon-o-swatch')
            ->navigationSort(100)
            ->navigationGroup('Appearance');

        expect($plugin->getNavigationIcon())->toBe('heroicon-o-swatch')
            ->and($plugin->getNavigationSort())->toBe(100)
            ->and($plugin->getNavigationGroup())->toBe('Appearance');
    });

    it('can configure widgets', function () {
        $plugin = FilamentThemesManagerPlugin::make()
            ->widgets([
                ThemesOverview::class,
            ]);

        expect($plugin->getWidgets())->toContain(ThemesOverview::class);
    });

    it('has correct plugin id', function () {
        expect($this->plugin->getId())->toBe('filament-themes-manager');
    });
});