<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Tests;

use Alizharb\FilamentThemesManager\FilamentThemesManagerServiceProvider;
use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Qirolab\Theme\ThemeServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->loadLaravelMigrations();
        $this->setupThemesDirectory();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            ThemeServiceProvider::class,
            FilamentThemesManagerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('theme.base_path', $this->getThemesPath());
        config()->set('theme.active', 'default');
        config()->set('filament-themes-manager.discovery.paths.themes', $this->getThemesPath());
        config()->set('filament-themes-manager.discovery.cache_duration', 0);
        config()->set('filament-themes-manager.security.protected_themes', ['default']);
    }

    protected function getThemesPath(): string
    {
        return __DIR__ . '/fixtures/themes';
    }

    protected function setupThemesDirectory(): void
    {
        $themesPath = $this->getThemesPath();

        if (!file_exists($themesPath)) {
            mkdir($themesPath, 0755, true);
        }

        // Create default theme
        $this->createTestTheme('default', [
            'name' => 'Default Theme',
            'description' => 'The default theme for testing',
            'version' => '1.0.0',
            'author' => 'Test Author',
            'author_email' => 'test@example.com',
            'homepage' => 'https://example.com',
            'license' => 'MIT',
            'screenshot' => 'screenshot.png',
            'requirements' => [
                'php' => '8.2',
                'laravel' => '11.0',
            ],
            'assets' => [
                'css/app.css',
                'js/app.js',
            ],
            'supports' => [
                'dark-mode',
                'responsive',
            ],
        ]);

        // Create test theme
        $this->createTestTheme('test-theme', [
            'name' => 'Test Theme',
            'description' => 'A theme for testing purposes',
            'version' => '2.1.0',
            'author' => 'Test Developer',
            'parent' => 'default',
        ]);

        // Create invalid theme (missing required fields)
        $this->createTestTheme('invalid-theme', [
            'description' => 'Missing name and version',
        ]);
    }

    protected function createTestTheme(string $slug, array $config): void
    {
        $themePath = $this->getThemesPath() . '/' . $slug;

        if (!file_exists($themePath)) {
            mkdir($themePath, 0755, true);
        }

        // Create theme.json
        file_put_contents(
            $themePath . '/theme.json',
            json_encode(array_merge(['slug' => $slug], $config), JSON_PRETTY_PRINT)
        );

        // Create views directory
        $viewsPath = $themePath . '/views';
        if (!file_exists($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }

        // Create sample view file
        file_put_contents(
            $viewsPath . '/welcome.blade.php',
            '<html><body><h1>{{ $title ?? "Welcome to ' . $slug . '" }}</h1></body></html>'
        );

        // Create assets if specified
        if (isset($config['assets'])) {
            foreach ($config['assets'] as $asset) {
                $assetPath = $themePath . '/' . $asset;
                $assetDir = dirname($assetPath);

                if (!file_exists($assetDir)) {
                    mkdir($assetDir, 0755, true);
                }

                file_put_contents($assetPath, '/* Sample asset content */');
            }
        }

        // Create screenshot if specified
        if (isset($config['screenshot'])) {
            file_put_contents(
                $themePath . '/' . $config['screenshot'],
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==')
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanupThemesDirectory();
        parent::tearDown();
    }

    protected function cleanupThemesDirectory(): void
    {
        $themesPath = $this->getThemesPath();

        if (file_exists($themesPath)) {
            $this->deleteDirectory($themesPath);
        }
    }

    protected function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}