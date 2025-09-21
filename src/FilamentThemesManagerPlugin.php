<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentThemesManagerPlugin implements Plugin
{
    protected string $navigationIcon = 'heroicon-o-swatch';
    protected int $navigationSort = 0;
    protected ?string $navigationGroup = null;
    protected array $pages = [];
    protected array $widgets = [];

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

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;
        return $this;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;
        return $this;
    }

    public function getNavigationSort(): int
    {
        return $this->navigationSort;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;
        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getPages(): array
    {
        return array_merge([
            \Alizharb\FilamentThemesManager\Pages\ThemeManager::class,
        ], $this->pages);
    }

    public function pages(array $pages): static
    {
        $this->pages = $pages;
        return $this;
    }

    public function getWidgets(): array
    {
        return array_merge(
            config('filament-themes-manager.widgets.widgets', []),
            $this->widgets
        );
    }

    public function widgets(array $widgets): static
    {
        $this->widgets = $widgets;
        return $this;
    }
}
