<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Commands\ThemeInstallCommand;
use Alizharb\FilamentThemesManager\Commands\ThemeCloneCommand;
use Illuminate\Support\Facades\File;

describe('Theme Commands', function () {
    it('can install theme command', function () {
        $command = new ThemeInstallCommand();

        expect($command->getName())->toBe('theme:install')
            ->and($command->getDescription())->toContain('Install a theme');
    });

    it('can clone theme command', function () {
        $command = new ThemeCloneCommand();

        expect($command->getName())->toBe('theme:clone')
<<<<<<< HEAD
            ->and($command->getDescription())->toContain('Clone an existing theme');
    });

=======
            ->and($command->getDescription())->toContain('Clone a theme');
    });

    it('install command has correct signature', function () {
        $this->artisan('theme:install --help')
            ->expectsOutput('Install a theme from various sources')
            ->assertExitCode(0);
    });

    it('clone command has correct signature', function () {
        $this->artisan('theme:clone --help')
            ->expectsOutput('Clone an existing theme')
            ->assertExitCode(0);
    });
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc

    it('can clone theme via command', function () {
        $newThemeSlug = 'command-cloned-theme';
        $themesPath = __DIR__ . '/../fixtures/themes';
        $newThemePath = $themesPath . '/' . $newThemeSlug;

        $this->artisan('theme:clone', [
            'source' => 'default',
<<<<<<< HEAD
            'name' => 'Command Cloned Theme',
            '--slug' => $newThemeSlug,
=======
            'target' => $newThemeSlug,
            '--name' => 'Command Cloned Theme',
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc
        ])->assertExitCode(0);

        expect(File::exists($newThemePath))->toBeTrue()
            ->and(File::exists($newThemePath . '/theme.json'))->toBeTrue();

        $themeConfig = json_decode(File::get($newThemePath . '/theme.json'), true);
        expect($themeConfig)
            ->toHaveKey('name', 'Command Cloned Theme')
            ->toHaveKey('slug', $newThemeSlug);

        // Cleanup
        if (File::exists($newThemePath)) {
            File::deleteDirectory($newThemePath);
        }
    });

    it('clone command fails with invalid source', function () {
        $this->artisan('theme:clone', [
            'source' => 'non-existent-theme',
<<<<<<< HEAD
            'name' => 'New Theme',
            '--slug' => 'new-theme',
=======
            'target' => 'new-theme',
            '--name' => 'New Theme',
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc
        ])->assertExitCode(1);
    });

    it('clone command fails with existing target', function () {
        $this->artisan('theme:clone', [
            'source' => 'default',
<<<<<<< HEAD
            'name' => 'Existing Theme',
            '--slug' => 'test-theme', // Already exists
=======
            'target' => 'test-theme', // Already exists
            '--name' => 'Existing Theme',
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc
        ])->assertExitCode(1);
    });

    it('install command can install from zip', function () {
        // Create a test ZIP file
        $tempDir = sys_get_temp_dir() . '/test_theme_command_' . uniqid();
        mkdir($tempDir, 0755, true);

        file_put_contents($tempDir . '/theme.json', json_encode([
            'name' => 'Command Installed Theme',
            'slug' => 'command-installed-theme',
            'version' => '1.0.0',
        ]));

        mkdir($tempDir . '/views', 0755, true);
        file_put_contents($tempDir . '/views/test.blade.php', '<div>Command Theme</div>');

        $zipPath = sys_get_temp_dir() . '/test_theme_command_' . uniqid() . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

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

        $this->artisan('theme:install', [
            'source' => $zipPath,
            '--type' => 'zip',
        ])->assertExitCode(0);

        // Cleanup
        unlink($zipPath);
        if (file_exists($tempDir)) {
            shell_exec("rm -rf " . escapeshellarg($tempDir));
        }

        $installedPath = __DIR__ . '/../fixtures/themes/command-installed-theme';
        if (File::exists($installedPath)) {
            File::deleteDirectory($installedPath);
        }
    });
});