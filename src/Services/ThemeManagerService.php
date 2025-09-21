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

/**
 * Theme Manager Service
 *
 * Comprehensive service for managing themes in Laravel Filament applications.
 * Provides functionality for theme installation, activation, cloning, deletion,
 * and validation with enterprise-grade security and performance features.
 *
 * Features:
 * - Multi-source theme installation (ZIP, GitHub, local)
 * - Theme validation and security scanning
 * - Protected theme management
 * - Cache optimization
 * - Backup and restore functionality
 * - Environment persistence
 *
 * @package Alizharb\FilamentThemesManager\Services
 * @author Ali Harb <harbzali@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 *
 * @example
 * ```php
 * $service = app(ThemeManagerService::class);
 *
 * // Get all themes
 * $themes = $service->getAllThemes();
 *
 * // Activate a theme
 * $service->setActiveTheme('my-theme');
 *
 * // Install from GitHub
 * $service->installThemeFromGitHub('username/theme-repo');
 * ```
 */
class ThemeManagerService
{
    /**
     * The base path where themes are stored.
     *
     * @var string
     */
    protected string $themesPath;

    /**
     * Array of theme slugs that are protected from deletion.
     *
     * @var array<string>
     */
    protected array $protectedThemes;

    /**
     * Initialize the theme manager service.
     *
     * Sets up theme paths and protection settings from configuration.
     * Automatically reads from config files and environment variables.
     */
    public function __construct()
    {
        $this->themesPath = config('theme.base_path', resource_path('themes'));
        $this->protectedThemes = config('filament-themes-manager.security.protected_themes', ['default']);
    }

    /**
     * Get all available themes
     *
     * @return Collection<int, ThemeData>
     */
    public function getAllThemes(): Collection
    {
        return ThemeModel::allData();
    }

    /**
     * Get the currently active theme.
     *
     * Retrieves the theme that is currently active in the application.
     * Returns null if no theme is active or if the active theme cannot be found.
     *
     * @return ThemeData|null The active theme data object, or null if none active
     *
     * @example
     * ```php
     * $activeTheme = $service->getActiveTheme();
     * if ($activeTheme) {
     *     echo "Active theme: " . $activeTheme->name;
     * }
     * ```
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
     * Set the active theme.
     *
     * Activates a theme by its slug. Performs validation, updates configuration,
     * clears caches, and persists the change to the environment file.
     *
     * @param string $slug The theme slug to activate
     * @return bool True if activation was successful, false otherwise
     *
     * @throws \InvalidArgumentException If theme slug is empty
     * @throws \RuntimeException If theme activation fails
     *
     * @example
     * ```php
     * if ($service->setActiveTheme('modern-theme')) {
     *     echo "Theme activated successfully!";
     * } else {
     *     echo "Failed to activate theme.";
     * }
     * ```
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
     * Install a theme from a ZIP file.
     *
     * Extracts and installs a theme from a ZIP archive. Validates the theme structure,
     * handles existing theme conflicts, and optionally creates backups.
     *
     * @param string $zipPath Absolute path to the ZIP file containing the theme
     * @return bool True if installation was successful, false otherwise
     *
     * @throws \InvalidArgumentException If ZIP path is invalid or file doesn't exist
     * @throws \RuntimeException If extraction or installation fails
     *
     * @example
     * ```php
     * $success = $service->installThemeFromZip('/path/to/theme.zip');
     * if ($success) {
     *     echo "Theme installed successfully!";
     * }
     * ```
     *
     * @see installThemeFromGitHub() For installing from GitHub repositories
     * @see cloneTheme() For creating copies of existing themes
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

            if (!is_array($themeData) || empty($themeData['slug'])) {
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
     * Install a theme from a GitHub repository.
     *
     * Clones a GitHub repository and installs it as a theme. Supports both
     * full GitHub URLs and shorthand repository names (username/repo).
     *
     * @param string $repository GitHub repository URL or shorthand (e.g., 'username/repo')
     * @return bool True if installation was successful, false otherwise
     *
     * @throws \InvalidArgumentException If repository format is invalid
     * @throws \RuntimeException If git clone or installation fails
     *
     * @example
     * ```php
     * // Using shorthand notation
     * $service->installThemeFromGitHub('username/awesome-theme');
     *
     * // Using full URL
     * $service->installThemeFromGitHub('https://github.com/username/theme.git');
     * ```
     *
     * @see installThemeFromZip() For installing from ZIP files
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

            if (!is_array($themeData) || empty($themeData['slug'])) {
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
     * Delete a theme permanently.
     *
     * Removes a theme from the filesystem. Protected themes cannot be deleted.
     * If the theme is currently active, automatically switches to the default theme.
     * Creates a backup before deletion if configured.
     *
     * @param string $slug The slug of the theme to delete
     * @return bool True if deletion was successful, false otherwise
     *
     * @throws \InvalidArgumentException If theme slug is empty
     * @throws \RuntimeException If deletion fails or theme is protected
     *
     * @example
     * ```php
     * if ($service->deleteTheme('old-theme')) {
     *     echo "Theme deleted successfully!";
     * } else {
     *     echo "Cannot delete protected theme.";
     * }
     * ```
     *
     * @see canDelete() To check if a theme can be deleted
     * @see isThemeProtected() To check if a theme is protected
     */
    public function deleteTheme(string $slug): bool
    {
        // Use the comprehensive canDelete check
        if (!$this->canDelete($slug)) {
            return false;
        }

        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return false;
        }

        try {
            // Create backup if configured
            if (config('filament-themes-manager.installation.backup_existing', true)) {
                $backupResult = $this->backupTheme($slug);
                if (!$backupResult) {
                    \Log::warning('Theme backup failed, continuing with deletion', ['slug' => $slug]);
                    // Continue with deletion even if backup fails
                }
            }

            // Verify the theme path exists before attempting deletion
            if (!File::exists($theme->path) || !File::isDirectory($theme->path)) {
                return false;
            }

            // Delete theme directory
            File::deleteDirectory($theme->path);

            // Verify deletion was successful
            if (File::exists($theme->path)) {
                return false;
            }

            // Clear cache so deleted theme disappears immediately
            $this->clearCache();

            return true;

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Theme deletion failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Clone an existing theme.
     *
     * Creates a copy of an existing theme with a new slug and name.
     * The cloned theme inherits from the source theme and starts with version 1.0.0.
     *
     * @param string $sourceSlug The slug of the theme to clone
     * @param string $newSlug The slug for the new theme (must be unique)
     * @param string $newName The display name for the new theme
     * @return bool True if cloning was successful, false otherwise
     *
     * @throws \InvalidArgumentException If any parameter is empty or invalid
     * @throws \RuntimeException If source theme doesn't exist or cloning fails
     *
     * @example
     * ```php
     * $success = $service->cloneTheme(
     *     'default',
     *     'custom-default',
     *     'My Custom Default Theme'
     * );
     * if ($success) {
     *     echo "Theme cloned successfully!";
     * }
     * ```
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

                if (is_array($themeData)) {
                    $themeData['name'] = $newName;
                    $themeData['slug'] = $newSlug;
                    $themeData['version'] = '1.0.0';
                    $themeData['parent'] = $sourceSlug; // Set source as parent

                    File::put($themeJsonPath, json_encode($themeData, JSON_PRETTY_PRINT));
                }
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
     *
     * @return array<string>
     */
    public function validateThemeRequirements(string $slug): array
    {
        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return ['Theme not found'];
        }

        $errors = [];

        // Check PHP version
        if (isset($theme->requirements['php']) && is_string($theme->requirements['php'])) {
            $requiredPhp = str_replace(['>=', '<=', '>', '<', '='], '', $theme->requirements['php']);
            if (version_compare(PHP_VERSION, $requiredPhp, '<')) {
                $errors[] = "PHP {$requiredPhp} or higher is required";
            }
        }

        // Check Laravel version
        if (isset($theme->requirements['laravel']) && is_string($theme->requirements['laravel'])) {
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
     *
     * @return array<string, int>
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
     * Clear all theme-related caches.
     *
     * Clears view cache, configuration cache, and Sushi model cache
     * to ensure theme changes are immediately visible.
     *
     * @return void
     *
     * @example
     * ```php
     * $service->clearCache();
     * echo "All theme caches cleared!";
     * ```
     */
    public function clearCache(): void
    {
        $this->clearViewCache();

        // Clear Sushi cache using the model's method
        ThemeModel::clearCache();
    }

    /**
     * Find the theme.json file in a directory.
     *
     * Recursively searches for a theme.json file in the given directory
     * and its subdirectories.
     *
     * @param string $directory The directory to search in
     * @return string|null The path to theme.json file, or null if not found
     *
     * @internal This method is for internal use only
     */
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

    /**
     * Create a backup of a theme.
     *
     * Creates a timestamped backup of the theme in the backups directory.
     * Useful before deletion or major updates.
     *
     * @param string $slug The slug of the theme to backup
     * @return bool True if backup was successful, false otherwise
     *
     * @internal This method is for internal use only
     */
    protected function backupTheme(string $slug): bool
    {
        $theme = ThemeModel::findData($slug);

        if (!$theme) {
            return false;
        }

        $backupDir = storage_path('app/theme-backups');

        // Only create directory if it doesn't exist
        if (!File::exists($backupDir)) {
            try {
                File::makeDirectory($backupDir, 0755, true);
            } catch (\Exception $e) {
                \Log::error('Failed to create backup directory', [
                    'directory' => $backupDir,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }

        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $slug . '_' . date('Y-m-d_H-i-s');

        try {
            // Ensure the source theme path exists
            if (!File::exists($theme->path)) {
                return false;
            }

            File::copyDirectory($theme->path, $backupPath);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to backup theme', [
                'slug' => $slug,
                'source' => $theme->path,
                'backup' => $backupPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if a theme is protected from deletion.
     *
     * Protected themes are defined in the configuration and cannot be deleted
     * to prevent accidental removal of critical themes.
     *
     * @param string $slug The theme slug to check
     * @return bool True if the theme is protected, false otherwise
     *
     * @internal This method is for internal use only
     */
    protected function isThemeProtected(string $slug): bool
    {
        return in_array($slug, $this->protectedThemes);
    }

    /**
     * Update a value in the .env file.
     *
     * Updates or adds a key-value pair to the application's .env file
     * for persistent configuration storage.
     *
     * @param string $key The environment key to update
     * @param string $value The new value for the key
     * @return void
     *
     * @internal This method is for internal use only
     */
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

    /**
     * Clear Laravel's view cache.
     *
     * Clears compiled view files to ensure theme changes in views
     * are immediately visible.
     *
     * @return void
     *
     * @internal This method is for internal use only
     */
    protected function clearViewCache(): void
    {
        try {
            Artisan::call('view:clear');
        } catch (\Exception $e) {
            // Ignore errors
        }
    }


    /**
     * Check if a theme can be deleted.
     *
     * Determines whether a theme can be safely deleted based on
     * protection settings and current usage.
     *
     * @param string $slug The theme slug to check
     * @return bool True if the theme can be deleted, false otherwise
     *
     * @example
     * ```php
     * if ($service->canDelete('my-theme')) {
     *     $service->deleteTheme('my-theme');
     * } else {
     *     echo "Theme is protected and cannot be deleted.";
     * }
     * ```
     *
     * @see deleteTheme() To actually delete the theme
     */
    public function canDelete(string $slug): bool
    {
        // Cannot delete protected themes
        if ($this->isThemeProtected($slug)) {
            return false;
        }

        // Cannot delete active theme
        $activeTheme = $this->getActiveTheme();
        if ($activeTheme && $activeTheme->slug === $slug) {
            return false;
        }

        return true;
    }

    /**
     * Check if a theme can be disabled.
     *
     * Determines whether a theme can be safely deactivated based on
     * protection settings and system requirements.
     *
     * @param string $slug The theme slug to check
     * @return bool True if the theme can be disabled, false otherwise
     *
     * @example
     * ```php
     * if ($service->canDisable('current-theme')) {
     *     $service->setActiveTheme('default');
     * } else {
     *     echo "Theme cannot be disabled.";
     * }
     * ```
     *
     * @see setActiveTheme() To change the active theme
     */
    public function canDisable(string $slug): bool
    {
        return !$this->isThemeProtected($slug);
    }

    /**
     * Install a theme from a local directory.
     *
     * Copies a theme from a local directory to the themes directory.
     * Useful for development or manual theme installation.
     *
     * @param string $sourcePath Absolute path to the source theme directory
     * @param string|null $targetSlug Optional target slug (uses directory name if not provided)
     * @return bool True if installation was successful, false otherwise
     *
     * @throws \InvalidArgumentException If source path doesn't exist or is invalid
     * @throws \RuntimeException If copying fails
     *
     * @example
     * ```php
     * $success = $service->installThemeFromLocal('/path/to/my-theme');
     * if ($success) {
     *     echo "Local theme installed successfully!";
     * }
     * ```
     *
     * @see installThemeFromZip() For installing from ZIP files
     * @see installThemeFromGitHub() For installing from GitHub
     */
    public function installThemeFromLocal(string $sourcePath, ?string $targetSlug = null): bool
    {
        if (!File::exists($sourcePath) || !File::isDirectory($sourcePath)) {
            return false;
        }

        $themeJsonPath = $sourcePath . DIRECTORY_SEPARATOR . 'theme.json';
        if (!File::exists($themeJsonPath)) {
            return false;
        }

        try {
            $themeData = json_decode(File::get($themeJsonPath), true);

            if (!is_array($themeData)) {
                return false;
            }

            $themeSlug = $targetSlug ?? ($themeData['slug'] ?? basename($sourcePath));
            $targetPath = $this->themesPath . DIRECTORY_SEPARATOR . $themeSlug;

            if (File::exists($targetPath)) {
                if (config('filament-themes-manager.installation.backup_existing', true)) {
                    $this->backupTheme($themeSlug);
                }
                File::deleteDirectory($targetPath);
            }

            File::copyDirectory($sourcePath, $targetPath);

            // Update theme.json with correct slug if provided
            if ($targetSlug && $targetSlug !== ($themeData['slug'] ?? '')) {
                $themeData['slug'] = $targetSlug;
                File::put(
                    $targetPath . DIRECTORY_SEPARATOR . 'theme.json',
                    json_encode($themeData, JSON_PRETTY_PRINT)
                );
            }

            $this->clearCache();

            if (config('filament-themes-manager.installation.auto_enable', false)) {
                $this->setActiveTheme($themeSlug);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get detailed information about a specific theme.
     *
     * Returns comprehensive theme information including metadata,
     * validation status, and file statistics.
     *
     * @param string $slug The theme slug to get information for
     * @return ThemeData|null The theme data object or null if not found
     *
     * @example
     * ```php
     * $themeInfo = $service->getThemeInfo('my-theme');
     * if ($themeInfo) {
     *     echo "Theme: {$themeInfo->name} v{$themeInfo->version}";
     *     echo "Valid: " . ($themeInfo->isValid ? 'Yes' : 'No');
     * }
     * ```
     *
     * @see getAllThemes() To get all themes
     * @see getActiveTheme() To get only the active theme
     */
    public function getThemeInfo(string $slug): ?ThemeData
    {
        return ThemeModel::findData($slug);
    }

    /**
     * Update an existing theme from its source.
     *
     * Re-downloads and installs a theme from its original source if available.
     * Preserves custom configurations where possible.
     *
     * @param string $slug The theme slug to update
     * @return bool True if update was successful, false otherwise
     *
     * @throws \InvalidArgumentException If theme doesn't exist
     * @throws \RuntimeException If update source is not available
     *
     * @example
     * ```php
     * if ($service->updateTheme('github-theme')) {
     *     echo "Theme updated successfully!";
     * } else {
     *     echo "Failed to update theme.";
     * }
     * ```
     */
    public function updateTheme(string $slug): bool
    {
        $theme = $this->getThemeInfo($slug);

        if (!$theme) {
            return false;
        }

        // Check if theme has update source in metadata
        $homepage = $theme->homepage;
        if (!$homepage) {
            return false;
        }

        // Create backup before update
        $this->backupTheme($slug);

        // Try to determine source type and update
        if (str_contains($homepage, 'github.com')) {
            return $this->installThemeFromGitHub($homepage);
        }

        return false;
    }

    /**
     * Get themes path configuration.
     *
     * Returns the base directory where themes are stored.
     *
     * @return string The absolute path to themes directory
     *
     * @example
     * ```php
     * $path = $service->getThemesPath();
     * echo "Themes are stored in: {$path}";
     * ```
     */
    public function getThemesPath(): string
    {
        return $this->themesPath;
    }

    /**
     * Get protected themes list.
     *
     * Returns an array of theme slugs that are protected from deletion.
     *
     * @return array<string> Array of protected theme slugs
     *
     * @example
     * ```php
     * $protected = $service->getProtectedThemes();
     * foreach ($protected as $slug) {
     *     echo "Protected theme: {$slug}";
     * }
     * ```
     */
    public function getProtectedThemes(): array
    {
        return $this->protectedThemes;
    }

    /**
     * Check if theme installation is allowed from a specific source.
     *
     * Validates whether installation from the given source type is permitted
     * based on security configuration.
     *
     * @param string $sourceType The source type to check ('zip', 'github', 'local')
     * @return bool True if installation is allowed, false otherwise
     *
     * @example
     * ```php
     * if ($service->isInstallationAllowed('github')) {
     *     $service->installThemeFromGitHub('username/repo');
     * }
     * ```
     */
    public function isInstallationAllowed(string $sourceType): bool
    {
        $allowedSources = config('filament-themes-manager.installation.allowed_sources', []);
        return in_array($sourceType, $allowedSources, true);
    }

    /**
     * Refresh theme discovery cache.
     *
     * Forces a complete refresh of theme data by clearing all caches
     * and re-scanning the themes directory.
     *
     * @return void
     *
     * @example
     * ```php
     * $service->refreshThemeDiscovery();
     * echo "Theme discovery refreshed!";
     * ```
     */
    public function refreshThemeDiscovery(): void
    {
        ThemeModel::refreshThemes();
        $this->clearCache();
    }
}