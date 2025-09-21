<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Services\ThemeManagerService;
use Alizharb\FilamentThemesManager\Models\Theme;
use Alizharb\FilamentThemesManager\Data\ThemeData;
use Illuminate\Support\Facades\File;

describe('ThemeManagerService', function () {
    beforeEach(function () {
        $this->service = new ThemeManagerService();
        Theme::clearCache();
    });

    it('can get all themes', function () {
        $themes = $this->service->getAllThemes();

        expect($themes)->toHaveCount(3)
            ->and($themes->first())->toBeInstanceOf(ThemeData::class);
    });

    it('can get active theme', function () {
        $activeTheme = $this->service->getActiveTheme();

        expect($activeTheme)
            ->toBeInstanceOf(ThemeData::class)
            ->and($activeTheme->slug)->toBe('default')
            ->and($activeTheme->active)->toBeTrue();
    });

    it('can set active theme', function () {
        $result = $this->service->setActiveTheme('test-theme');

        expect($result)->toBeTrue();

        $activeTheme = $this->service->getActiveTheme();
        expect($activeTheme->slug)->toBe('test-theme');
    });

    it('cannot set invalid theme as active', function () {
        $result = $this->service->setActiveTheme('invalid-theme');

        expect($result)->toBeFalse();
    });

    it('cannot set non-existent theme as active', function () {
        $result = $this->service->setActiveTheme('non-existent');

        expect($result)->toBeFalse();
    });

    it('can clone a theme', function () {
        $themesPath = $this->getThemesPath();
        $newThemeSlug = 'cloned-theme';
        $newThemePath = $themesPath . '/' . $newThemeSlug;

        $result = $this->service->cloneTheme('default', $newThemeSlug, 'Cloned Theme');

        expect($result)->toBeTrue()
            ->and(File::exists($newThemePath))->toBeTrue()
            ->and(File::exists($newThemePath . '/theme.json'))->toBeTrue();

        $themeConfig = json_decode(File::get($newThemePath . '/theme.json'), true);
        expect($themeConfig)
            ->toHaveKey('name', 'Cloned Theme')
            ->toHaveKey('slug', $newThemeSlug)
            ->toHaveKey('parent', 'default');

        // Cleanup
        if (File::exists($newThemePath)) {
            File::deleteDirectory($newThemePath);
        }
    });

    it('cannot clone non-existent theme', function () {
        $result = $this->service->cloneTheme('non-existent', 'new-theme', 'New Theme');

        expect($result)->toBeFalse();
    });

    it('cannot clone to existing theme slug', function () {
        $result = $this->service->cloneTheme('default', 'test-theme', 'Existing Theme');

        expect($result)->toBeFalse();
    });

    it('can delete unprotected theme', function () {
        // First create a theme that's not protected
        $this->createTestTheme('deletable-theme', [
            'name' => 'Deletable Theme',
            'version' => '1.0.0',
        ]);

        $result = $this->service->deleteTheme('deletable-theme');

        expect($result)->toBeTrue();

        // Check that the theme directory has been removed
        $themePath = __DIR__ . '/../fixtures/themes/deletable-theme';
        expect(file_exists($themePath))->toBeFalse();
    });

    it('cannot delete protected theme', function () {
        $result = $this->service->deleteTheme('default');

        expect($result)->toBeFalse();

        $theme = Theme::find('default');
        expect($theme)->not->toBeNull();
    });

    it('cannot delete non-existent theme', function () {
        $result = $this->service->deleteTheme('non-existent');

        expect($result)->toBeFalse();
    });

    it('can validate theme requirements', function () {
        $errors = $this->service->validateThemeRequirements('default');

        expect($errors)->toBeArray()->toBeEmpty();
    });

    it('can detect unmet PHP requirements', function () {
        // Create a theme with higher PHP requirement
        $this->createTestTheme('high-php-theme', [
            'name' => 'High PHP Theme',
            'version' => '1.0.0',
            'requirements' => [
                'php' => '9.0',
            ],
        ]);

        Theme::clearCache(); // Refresh to include new theme

        $errors = $this->service->validateThemeRequirements('high-php-theme');

        expect($errors)
            ->toBeArray()
            ->not->toBeEmpty()
            ->toContain('PHP 9.0 or higher is required');

        // Cleanup
        $themePath = $this->getThemesPath() . '/high-php-theme';
        if (File::exists($themePath)) {
            File::deleteDirectory($themePath);
        }
    });

    it('returns validation error for non-existent theme', function () {
        $errors = $this->service->validateThemeRequirements('non-existent');

        expect($errors)
            ->toBeArray()
            ->toContain('Theme not found');
    });

    it('can get theme statistics', function () {
        $stats = $this->service->getThemeStats();

        expect($stats)
            ->toBeArray()
            ->toHaveKey('total', 3)
            ->toHaveKey('active', 1)
            ->toHaveKey('valid', 2)
            ->toHaveKey('invalid', 1);
    });

    it('can clear cache', function () {
        expect(fn() => $this->service->clearCache())->not->toThrow(\Exception::class);
    });

    it('can check if theme can be deleted', function () {
        expect($this->service->canDelete('default'))->toBeFalse()
            ->and($this->service->canDelete('test-theme'))->toBeTrue();
    });

    it('can check if theme can be disabled', function () {
        expect($this->service->canDisable('default'))->toBeFalse()
            ->and($this->service->canDisable('test-theme'))->toBeTrue();
    });

    it('can install theme from zip', function () {
        // Create a test ZIP file
        $tempDir = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempDir, 0755, true);

        // Create theme structure in temp dir
        file_put_contents($tempDir . '/theme.json', json_encode([
            'name' => 'ZIP Theme',
            'slug' => 'zip-theme',
            'version' => '1.0.0',
        ]));

        mkdir($tempDir . '/views', 0755, true);
        file_put_contents($tempDir . '/views/test.blade.php', '<div>ZIP Theme</div>');

        // Create ZIP file
        $zipPath = sys_get_temp_dir() . '/test_theme_' . uniqid() . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $relativePath = str_replace($tempDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $zip->addFile($file->getRealPath(), $relativePath);
            }
        }

        $zip->close();

        // Test installation
        $result = $this->service->installThemeFromZip($zipPath);

        expect($result)->toBeTrue();

        // Verify theme was installed
        Theme::clearCache();
        $installedTheme = Theme::find('zip-theme');
        expect($installedTheme)->not->toBeNull()
            ->and($installedTheme->name)->toBe('ZIP Theme');

        // Cleanup
        unlink($zipPath);
        if (file_exists($tempDir)) {
            shell_exec("rm -rf " . escapeshellarg($tempDir));
        }
        $installedPath = $this->getThemesPath() . '/zip-theme';
        if (File::exists($installedPath)) {
            File::deleteDirectory($installedPath);
        }
    });

    it('fails to install invalid zip file', function () {
        // Create an invalid ZIP file (empty file)
        $invalidZipPath = sys_get_temp_dir() . '/invalid_' . uniqid() . '.zip';
        file_put_contents($invalidZipPath, 'not a zip file');

        $result = $this->service->installThemeFromZip($invalidZipPath);

        expect($result)->toBeFalse();

        // Cleanup
        unlink($invalidZipPath);
    });

    it('fails to install from non-existent zip file', function () {
        $result = $this->service->installThemeFromZip('/non/existent/file.zip');

        expect($result)->toBeFalse();
    });
});

// Helper function to get themes path
function getThemesPath(): string
{
    return __DIR__ . '/../fixtures/themes';
}

// Helper function to create test theme
function createTestTheme(string $slug, array $config): void
{
    $themePath = getThemesPath() . '/' . $slug;

    if (!file_exists($themePath)) {
        mkdir($themePath, 0755, true);
    }

    file_put_contents(
        $themePath . '/theme.json',
        json_encode(array_merge(['slug' => $slug], $config), JSON_PRETTY_PRINT)
    );

    $viewsPath = $themePath . '/views';
    if (!file_exists($viewsPath)) {
        mkdir($viewsPath, 0755, true);
    }

    file_put_contents(
        $viewsPath . '/test.blade.php',
        '<div>Test view for ' . $slug . '</div>'
    );
}