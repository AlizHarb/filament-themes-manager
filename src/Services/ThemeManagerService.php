<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;
use Qirolab\Theme\Theme;
use Alizharb\FilamentThemesManager\Models\Theme as ThemeModel;
use Alizharb\FilamentThemesManager\Data\ThemeData;
use ZipArchive;

class ThemeManagerService
{
    protected string $themesPath;
    protected array $protectedThemes;

    public function __construct()
    {
        $this->themesPath = config('theme.base_path', resource_path('themes'));
        $this->protectedThemes = config('filament-themes-manager.security.protected_themes', ['default']);
    }

    /**
     * Get all available themes
     */
    public function getAllThemes(): Collection
    {
        return ThemeModel::allData();
    }

    /**
     * Get the currently active theme
     */
    public function getActiveTheme(): ?ThemeData
    {
        $activeSlug = Theme::active() ?? config('theme.active');

        if (!$activeSlug) {
            return null;
        }

        return ThemeModel::findData($activeSlug);
    }

    /**
     * Set the active theme
     */
    public function setActiveTheme(string $slug): bool
    {
        $theme = ThemeModel::findData($slug);

        if (!$theme || !$theme->isValid) {
            return false;
        }

        try {
            // Clear existing theme first
            Theme::clear();

            // Set the new theme using the facade
            Theme::set($slug, $theme->parent);

            // Update configuration in memory
            Config::set('theme.active', $slug);

            // Update .env file for persistence
            $this->updateEnvFile('ACTIVE_THEME', $slug);

            // Clear all caches to ensure fresh data
            $this->clearViewCache();
            ThemeModel::clearCache();

            // Force refresh config cache
            if (function_exists('config_clear')) {
                \Artisan::call('config:clear');
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Install a theme from ZIP file
     */
    public function installThemeFromZip(string $zipPath): bool
    {
        if (!File::exists($zipPath)) {
            return false;
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== TRUE) {
            return false;
        }

        try {
            // Create temporary extraction directory
            $tempDir = storage_path('app/temp/themes/' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            // Extract to temp directory
            $zip->extractTo($tempDir);
            $zip->close();

            // Find theme.json in extracted files
            $themeJsonPath = $this->findThemeJson($tempDir);

            if (!$themeJsonPath) {
                File::deleteDirectory($tempDir);
                return false;
            }

            $themeData = json_decode(File::get($themeJsonPath), true);

            if (!$themeData || empty($themeData['slug'])) {
                File::deleteDirectory($tempDir);
                return false;
            }

            $themeSlug = $themeData['slug'];
            $themeDir = dirname($themeJsonPath);

            // Check if theme already exists
            $targetPath = $this->themesPath . DIRECTORY_SEPARATOR . $themeSlug;

            if (File::exists($targetPath)) {
                if (config('filament-themes-manager.installation.backup_existing', true)) {
                    $this->backupTheme($themeSlug);
                }
                File::deleteDirectory($targetPath);
            }

            // Move theme to themes directory
            File::moveDirectory($themeDir, $targetPath);

            // Clean up temp directory
            File::deleteDirectory($tempDir);

            // Clear cache so new theme appears immediately
            $this->clearCache();

            // Auto-enable if configured
            if (config('filament-themes-manager.installation.auto_enable', false)) {
                $this->setActiveTheme($themeSlug);
            }

            return true;

        } catch (\Exception $e) {
            $zip->close();
            return false;
        }
    }

    /**
     * Install theme from GitHub repository
     */
    public function installThemeFromGitHub(string $repository): bool
    {
        // Handle both full URLs and shorthand repo names
        if (filter_var($repository, FILTER_VALIDATE_URL)) {
            $repoUrl = $repository;
        } else {
            $repoUrl = "https://github.com/{$repository}.git";
        }

        $tempDir = storage_path('app/temp/themes/' . uniqid());

        try {
            // Clone repository
            $command = "git clone {$repoUrl} {$tempDir}";
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return false;
            }

            // Find theme.json
            $themeJsonPath = $this->findThemeJson($tempDir);

            if (!$themeJsonPath) {
                File::deleteDirectory($tempDir);
                return false;
            }

            $themeData = json_decode(File::get($themeJsonPath), true);

            if (!$themeData || empty($themeData['slug'])) {
                File::deleteDirectory($tempDir);
                return false;
            }

            $themeSlug = $themeData['slug'];
            $targetPath = $this->themesPath . DIRECTORY_SEPARATOR . $themeSlug;

            // Check if theme already exists
            if (File::exists($targetPath)) {
                if (config('filament-themes-manager.installation.backup_existing', true)) {
                    $this->backupTheme($themeSlug);
                }
                File::deleteDirectory($targetPath);
            }

            // Remove git directory
            File::deleteDirectory($tempDir . DIRECTORY_SEPARATOR . '.git');

            // Move theme to themes directory
            File::moveDirectory($tempDir, $targetPath);

            // Clear cache so new theme appears immediately
            $this->clearCache();

            return true;

        } catch (\Exception $e) {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return false;
        }
    }

    /**
     * Delete a theme
     */
    public function deleteTheme(string $slug): bool
    {
        if ($this->isThemeProtected($slug)) {
            return false;
        }

        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return false;
        }

        try {
            // If this is the active theme, switch to default
            if ($theme->active) {
                $this->setActiveTheme('default');
            }

            // Create backup if configured
            if (config('filament-themes-manager.installation.backup_existing', true)) {
                $this->backupTheme($slug);
            }

            // Delete theme directory
            File::deleteDirectory($theme->path);

            // Clear cache so deleted theme disappears immediately
            $this->clearCache();

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clone an existing theme
     */
    public function cloneTheme(string $sourceSlug, string $newSlug, string $newName): bool
    {
        $sourceTheme = ThemeModel::findData($sourceSlug);

        if (!$sourceTheme) {
            return false;
        }

        $targetPath = $this->themesPath . DIRECTORY_SEPARATOR . $newSlug;

        if (File::exists($targetPath)) {
            return false; // Theme already exists
        }

        try {
            // Copy theme directory
            File::copyDirectory($sourceTheme->path, $targetPath);

            // Update theme.json
            $themeJsonPath = $targetPath . DIRECTORY_SEPARATOR . 'theme.json';

            if (File::exists($themeJsonPath)) {
                $themeData = json_decode(File::get($themeJsonPath), true);
                $themeData['name'] = $newName;
                $themeData['slug'] = $newSlug;
                $themeData['version'] = '1.0.0';
                $themeData['parent'] = $sourceSlug; // Set source as parent

                File::put($themeJsonPath, json_encode($themeData, JSON_PRETTY_PRINT));
            }

            // Clear cache so cloned theme appears immediately
            $this->clearCache();

            return true;

        } catch (\Exception $e) {
            if (File::exists($targetPath)) {
                File::deleteDirectory($targetPath);
            }
            return false;
        }
    }

    /**
     * Validate theme requirements
     */
    public function validateThemeRequirements(string $slug): array
    {
        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return ['Theme not found'];
        }

        $errors = [];

        // Check PHP version
        if (isset($theme->requirements['php'])) {
            $requiredPhp = str_replace(['>=', '<=', '>', '<', '='], '', $theme->requirements['php']);
            if (version_compare(PHP_VERSION, $requiredPhp, '<')) {
                $errors[] = "PHP {$requiredPhp} or higher is required";
            }
        }

        // Check Laravel version
        if (isset($theme->requirements['laravel'])) {
            $requiredLaravel = str_replace(['>=', '<=', '>', '<', '='], '', $theme->requirements['laravel']);
            $laravelVersion = app()->version();
            if (version_compare($laravelVersion, $requiredLaravel, '<')) {
                $errors[] = "Laravel {$requiredLaravel} or higher is required";
            }
        }

        return $errors;
    }

    /**
     * Get theme statistics
     */
    public function getThemeStats(): array
    {
        return [
            'total' => ThemeModel::count(),
            'active' => ThemeModel::getActiveCount(),
            'valid' => ThemeModel::getValidCount(),
            'invalid' => ThemeModel::getInvalidCount(),
        ];
    }

    /**
     * Clear theme cache
     */
    public function clearCache(): void
    {
        $this->clearViewCache();

        // Clear Sushi cache using the model's method
        ThemeModel::clearCache();
    }

    protected function findThemeJson(string $directory): ?string
    {
        $themeJsonPath = $directory . DIRECTORY_SEPARATOR . 'theme.json';

        if (File::exists($themeJsonPath)) {
            return $themeJsonPath;
        }

        // Search in subdirectories
        $subdirectories = File::directories($directory);

        foreach ($subdirectories as $subdir) {
            $result = $this->findThemeJson($subdir);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    protected function backupTheme(string $slug): bool
    {
        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return false;
        }

        $backupDir = storage_path('app/theme-backups');
        File::makeDirectory($backupDir, 0755, true);

        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $slug . '_' . date('Y-m-d_H-i-s');

        try {
            File::copyDirectory($theme->path, $backupPath);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function isThemeProtected(string $slug): bool
    {
        return in_array($slug, $this->protectedThemes);
    }

    protected function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}";
        }

        File::put($envPath, $envContent);
    }

    protected function clearViewCache(): void
    {
        try {
            Artisan::call('view:clear');
        } catch (\Exception $e) {
            // Ignore errors
        }
    }


    /**
     * Check if a theme can be deleted
     */
    public function canDelete(string $slug): bool
    {
        return !$this->isThemeProtected($slug);
    }

    /**
     * Check if a theme can be disabled
     */
    public function canDisable(string $slug): bool
    {
        return !$this->isThemeProtected($slug);
    }
}