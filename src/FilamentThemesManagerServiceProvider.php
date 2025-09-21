<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager;

use Illuminate\Support\ServiceProvider;
use Alizharb\FilamentThemesManager\Commands\{
    ThemeInstallCommand,
    ThemeCloneCommand,
    ThemeDebugCommand
};
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;

/**
 * Filament Themes Manager Service Provider.
 *
 * This service provider handles the registration and booting of the
 * Filament Themes Manager package, including configuration, commands,
 * views, translations, and routes.
 *
 * @package Alizharb\FilamentThemesManager
 * @author Ali Harb <harbzali@gmail.com>
 * @since 1.0.0
 */
class FilamentThemesManagerServiceProvider extends ServiceProvider
{
    /**
     * Package configuration key.
     */
    private const CONFIG_KEY = 'filament-themes-manager';

    /**
     * Package translation namespace.
     */
    private const TRANSLATION_NAMESPACE = 'filament-themes-manager';

    /**
     * Package view namespace.
     */
    private const VIEW_NAMESPACE = 'filament-themes-manager';

    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergePackageConfiguration();
        $this->registerServices();
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->bootTranslations();
        $this->bootViews();
        $this->bootRoutes();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Merge package configuration with application configuration.
     */
    private function mergePackageConfiguration(): void
    {
        $this->mergeConfigFrom(
            $this->getConfigPath(),
            self::CONFIG_KEY
        );
    }

    /**
     * Register package services in the container.
     */
    private function registerServices(): void
    {
        $this->app->singleton(ThemeManagerService::class);
    }

    /**
     * Boot translation resources.
     */
    private function bootTranslations(): void
    {
        $this->loadTranslationsFrom(
            $this->getLanguagePath(),
            self::TRANSLATION_NAMESPACE
        );
    }

    /**
     * Boot view resources.
     */
    private function bootViews(): void
    {
        $this->loadViewsFrom(
            $this->getViewsPath(),
            self::VIEW_NAMESPACE
        );
    }

    /**
     * Boot route resources.
     */
    private function bootRoutes(): void
    {
        $this->loadRoutesFrom($this->getRoutesPath());
    }

    /**
     * Boot console-specific features.
     */
    private function bootForConsole(): void
    {
        $this->publishConfiguration();
        $this->publishTranslations();
        $this->publishViews();
        $this->registerCommands();
    }

    /**
     * Publish package configuration.
     */
    private function publishConfiguration(): void
    {
        $this->publishes([
            $this->getConfigPath() => config_path(self::CONFIG_KEY . '.php'),
        ], self::CONFIG_KEY . '-config');
    }

    /**
     * Publish package translations.
     */
    private function publishTranslations(): void
    {
        $this->publishes([
            $this->getLanguagePath() => $this->app->langPath('vendor/' . self::TRANSLATION_NAMESPACE),
        ], self::CONFIG_KEY . '-translations');
    }

    /**
     * Publish package views.
     */
    private function publishViews(): void
    {
        $this->publishes([
            $this->getViewsPath() => resource_path('views/vendor/' . self::VIEW_NAMESPACE),
        ], self::CONFIG_KEY . '-views');
    }

    /**
     * Register package commands.
     */
    private function registerCommands(): void
    {
        $this->commands([
            ThemeInstallCommand::class,
            ThemeCloneCommand::class,
            ThemeDebugCommand::class,
        ]);
    }

    /**
     * Get the path to the package configuration file.
     */
    private function getConfigPath(): string
    {
        return __DIR__ . '/../config/' . self::CONFIG_KEY . '.php';
    }

    /**
     * Get the path to the package language directory.
     */
    private function getLanguagePath(): string
    {
        return __DIR__ . '/../resources/lang';
    }

    /**
     * Get the path to the package views directory.
     */
    private function getViewsPath(): string
    {
        return __DIR__ . '/../resources/views';
    }

    /**
     * Get the path to the package routes file.
     */
    private function getRoutesPath(): string
    {
        return __DIR__ . '/../routes/web.php';
    }
}