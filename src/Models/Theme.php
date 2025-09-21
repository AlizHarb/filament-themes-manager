<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Sushi\Sushi;
use Alizharb\FilamentThemesManager\Data\ThemeData;
use Qirolab\Theme\Theme as ThemeFacade;

/**
 * Theme model representing application themes with Sushi-powered in-memory storage.
 *
 * This model uses the Sushi package to create an in-memory SQLite database
 * populated with theme data from the filesystem. It provides a Laravel Eloquent
 * interface for managing themes without requiring a physical database table.
 *
 * @property string $name The human-readable name of the theme
 * @property string $slug The unique slug identifier for the theme
 * @property string|null $description A brief description of the theme
 * @property string $version The semantic version of the theme
 * @property string|null $author The author of the theme
 * @property string|null $author_email The author's email address
 * @property string|null $homepage The theme's homepage URL
 * @property string $path The filesystem path to the theme directory
 * @property bool $active Whether this theme is currently active
 * @property string|null $parent The slug of the parent theme (for child themes)
 * @property array<string, mixed> $requirements Theme requirements and dependencies
 * @property array<string, mixed> $assets Theme asset configuration
 * @property array<string, mixed> $supports Features supported by the theme
 * @property string|null $license The license under which the theme is distributed
 * @property string|null $screenshot The filename of the theme's screenshot
 * @property bool $is_valid Whether the theme passes validation checks
 * @property array<string> $errors List of validation errors (if any)
 * @property array<string, mixed> $metadata Additional theme metadata
 *
 * @method static Builder<self> active() Scope for active themes
 * @method static Builder<self> inactive() Scope for inactive themes
 * @method static Builder<self> byName(string $name) Scope to find by name
 * @method static Builder<self> bySlug(string $slug) Scope to find by slug
 * @method static Builder<self> valid() Scope for valid themes
 * @method static Builder<self> invalid() Scope for invalid themes
 *
 * @package Alizharb\FilamentThemesManager\Models
 * @author Ali Harb <harbzali@gmail.com>
 * @since 1.0.0
 */
class Theme extends Model
{
    use Sushi;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'slug';

    /**
     * The data type of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * The Sushi schema definition for the in-memory table.
     *
     * @var array<string, string>
     */
    protected $schema = [
        'name' => 'string',
        'slug' => 'string',
        'description' => 'text',
        'version' => 'string',
        'author' => 'string',
        'author_email' => 'string',
        'homepage' => 'string',
        'path' => 'string',
        'active' => 'boolean',
        'parent' => 'string',
        'requirements' => 'json',
        'assets' => 'json',
        'supports' => 'json',
        'license' => 'string',
        'screenshot' => 'string',
        'is_valid' => 'boolean',
        'errors' => 'json',
        'metadata' => 'json',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'is_valid' => 'boolean',
        'requirements' => 'array',
        'assets' => 'array',
        'supports' => 'array',
        'errors' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot the model and ensure Sushi connection is properly initialized.
     */
    protected static function boot(): void
    {
        parent::boot();

        if (!static::$sushiConnection) {
            static::bootSushi();
        }
    }

    /**
     * Get the rows for the Sushi-powered database with comprehensive theme data.
     *
     * This method scans the themes directory and builds an array of theme data
     * that will be used to populate the in-memory SQLite database.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        $themesPath = config('theme.base_path', resource_path('themes'));
        $activeTheme = ThemeFacade::active() ?? config('theme.active', 'default');

        if (!File::exists($themesPath)) {
            return [];
        }

        $themes = [];
        $directories = File::directories($themesPath);

        foreach ($directories as $directory) {
            $themeSlug = basename($directory);
            $themeJsonPath = $directory . DIRECTORY_SEPARATOR . 'theme.json';

            if (!File::exists($themeJsonPath)) {
                continue;
            }

            $themeData = $this->parseThemeJson($themeJsonPath);

            if (!$themeData) {
                continue;
            }

            $themeRealSlug = $themeData['slug'] ?? $themeSlug;

            $themes[] = [
                'name' => $themeData['name'] ?? $themeSlug,
                'slug' => $themeRealSlug,
                'description' => $themeData['description'] ?? null,
                'version' => $themeData['version'] ?? '1.0.0',
                'author' => $themeData['author'] ?? null,
                'author_email' => $themeData['author_email'] ?? null,
                'homepage' => $themeData['homepage'] ?? null,
                'path' => $directory,
                'active' => $activeTheme === $themeRealSlug,
                'parent' => $themeData['parent'] ?? null,
                'requirements' => json_encode($themeData['requirements'] ?? []),
                'assets' => json_encode($themeData['assets'] ?? []),
                'supports' => json_encode($themeData['supports'] ?? []),
                'license' => $themeData['license'] ?? null,
                'screenshot' => $themeData['screenshot'] ?? null,
                'is_valid' => $this->validateTheme($directory, $themeData),
                'errors' => json_encode($this->getThemeErrors($directory, $themeData)),
                'metadata' => json_encode($this->getThemeMetadata($directory)),
            ];
        }

        return $themes;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function newQuery(): Builder
    {
        if (!static::$sushiConnection) {
            static::bootSushi();
        }

        return parent::newQuery();
    }

    /**
     * Resolve a connection instance.
     *
     * @param string|null $connection The connection name
     * @return \Illuminate\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        if (!static::$sushiConnection) {
            static::bootSushi();
        }

        return static::$sushiConnection ?: parent::resolveConnection($connection);
    }

    /**
     * Parse theme.json file and return the configuration array.
     *
     * @param string $path The path to the theme.json file
     * @return array<string, mixed>|null The parsed theme data or null on failure
     */
    protected function parseThemeJson(string $path): ?array
    {
        try {
            $content = File::get($path);
            return json_decode($content, true);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Validate theme structure and configuration.
     *
     * @param string $directory The theme directory path
     * @param array<string, mixed> $themeData The parsed theme data
     * @return bool True if the theme is valid, false otherwise
     */
    protected function validateTheme(string $directory, array $themeData): bool
    {
        $errors = $this->getThemeErrors($directory, $themeData);
        return empty($errors);
    }

    /**
     * Get list of validation errors for the theme.
     *
     * @param string $directory The theme directory path
     * @param array<string, mixed> $themeData The parsed theme data
     * @return array<string> List of validation error messages
     */
    protected function getThemeErrors(string $directory, array $themeData): array
    {
        $errors = [];
        $requiredFields = config('filament-themes-manager.validation.required_fields', ['name', 'version']);

        foreach ($requiredFields as $field) {
            if (empty($themeData[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        $viewsPath = $directory . DIRECTORY_SEPARATOR . 'views';
        if (!File::exists($viewsPath)) {
            $errors[] = 'Views directory is missing';
        }

        if (!empty($themeData['assets'])) {
            foreach ($themeData['assets'] as $asset) {
                $assetPath = $directory . DIRECTORY_SEPARATOR . $asset;
                if (!File::exists($assetPath)) {
                    $errors[] = "Asset file not found: {$asset}";
                }
            }
        }

        return $errors;
    }

    /**
     * Generate metadata about the theme directory and files.
     *
     * @param string $directory The theme directory path
     * @return array<string, mixed> Theme metadata
     */
    protected function getThemeMetadata(string $directory): array
    {
        $metadata = [];

        $bladeFiles = File::glob($directory . '/**/*.blade.php');
        $metadata['blade_files_count'] = count($bladeFiles);

        $cssFiles = File::glob($directory . '/**/*.css');
        $metadata['css_files_count'] = count($cssFiles);

        $jsFiles = File::glob($directory . '/**/*.js');
        $metadata['js_files_count'] = count($jsFiles);

        $metadata['size'] = $this->getDirectorySize($directory);
        $metadata['last_modified'] = File::lastModified($directory);

        return $metadata;
    }

    /**
     * Calculate the total size of a directory in bytes.
     *
     * @param string $directory The directory path
     * @return int The total size in bytes
     */
    protected function getDirectorySize(string $directory): int
    {
        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Scope a query to only include active themes.
     *
     * @param Builder<self> $query The query builder instance
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include inactive themes.
     *
     * @param Builder<self> $query The query builder instance
     * @return Builder<self>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Scope a query to find themes by name.
     *
     * @param Builder<self> $query The query builder instance
     * @param string $name The name to search for
     * @return Builder<self>
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope a query to find themes by slug.
     *
     * @param Builder<self> $query The query builder instance
     * @param string $slug The slug to search for
     * @return Builder<self>
     */
    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope a query to only include valid themes.
     *
     * @param Builder<self> $query The query builder instance
     * @return Builder<self>
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope a query to only include invalid themes.
     *
     * @param Builder<self> $query The query builder instance
     * @return Builder<self>
     */
    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('is_valid', false);
    }

    /**
     * Get the cache key for Sushi's cached schema.
     *
     * @return string The cache reference path
     */
    protected function sushiCacheReferencePath(): string
    {
        $themesPath = config('theme.base_path', resource_path('themes'));
        $activeTheme = config('theme.active', 'default');

        return $themesPath . DIRECTORY_SEPARATOR . 'active-' . $activeTheme;
    }

    /**
     * Determine if Sushi should cache the schema.
     *
     * @return bool False to disable caching for real-time updates
     */
    public function sushiShouldCache(): bool
    {
        return false;
    }

    /**
     * Get the cache duration for Sushi's cached schema.
     *
     * @return int Cache duration in seconds
     */
    public function sushiCacheDuration(): int
    {
        return config('filament-themes-manager.discovery.cache_duration', 3600);
    }

    /**
     * Get all themes as typed ThemeData objects.
     *
     * @return Collection<int, ThemeData>
     */
    public static function allData(): Collection
    {
        return self::all()->map(fn (self $theme): ThemeData => $theme->toData());
    }

    /**
     * Find a theme by slug and return a ThemeData object.
     *
     * @param string $slug The theme slug
     * @return ThemeData|null The theme data object or null if not found
     */
    public static function findData(string $slug): ?ThemeData
    {
        $theme = self::find($slug);

        if (!$theme instanceof self) {
            return null;
        }

        return $theme->toData();
    }

    /**
     * Get active themes count.
     *
     * @return int Number of active themes
     */
    public static function getActiveCount(): int
    {
        return self::active()->count();
    }

    /**
     * Get inactive themes count.
     *
     * @return int Number of inactive themes
     */
    public static function getInactiveCount(): int
    {
        return self::inactive()->count();
    }

    /**
     * Get valid themes count.
     *
     * @return int Number of valid themes
     */
    public static function getValidCount(): int
    {
        return self::valid()->count();
    }

    /**
     * Get invalid themes count.
     *
     * @return int Number of invalid themes
     */
    public static function getInvalidCount(): int
    {
        return self::invalid()->count();
    }

    /**
     * Check if a theme exists by slug.
     *
     * @param string $slug The theme slug
     * @return bool True if the theme exists, false otherwise
     */
    public static function existsBySlug(string $slug): bool
    {
        return self::where('slug', $slug)->exists();
    }

    /**
     * Convert the model instance to ThemeData.
     *
     * @return ThemeData The theme data transfer object
     */
    public function toData(): ThemeData
    {
        return new ThemeData(
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            version: $this->version,
            author: $this->author,
            authorEmail: $this->author_email,
            homepage: $this->homepage,
            path: $this->path,
            active: $this->active,
            parent: $this->parent,
            requirements: $this->requirements ?? [],
            assets: $this->assets ?? [],
            supports: $this->supports ?? [],
            license: $this->license,
            screenshot: $this->screenshot,
            isValid: $this->is_valid,
            errors: $this->errors ?? [],
            metadata: $this->metadata ?? [],
        );
    }

    /**
     * Check if the theme is protected from deletion.
     *
     * @return bool True if the theme is protected, false otherwise
     */
    public function isProtected(): bool
    {
        return in_array($this->slug, config('filament-themes-manager.security.protected_themes', []));
    }

    /**
     * Check if the theme has a screenshot.
     *
     * @return bool True if the theme has a valid screenshot, false otherwise
     */
    public function hasScreenshot(): bool
    {
        return !empty($this->screenshot) && File::exists($this->path . DIRECTORY_SEPARATOR . $this->screenshot);
    }

    /**
     * Get the screenshot URL.
     *
     * @return string|null The screenshot URL or null if no screenshot exists
     */
    public function getScreenshotUrl(): ?string
    {
        if (!$this->hasScreenshot()) {
            return null;
        }

        $relativePath = str_replace(base_path(), '', $this->path . DIRECTORY_SEPARATOR . $this->screenshot);
        return asset(ltrim($relativePath, DIRECTORY_SEPARATOR));
    }

    /**
     * Clear the Sushi cache for themes.
     */
    public static function clearCache(): void
    {
        if (app()->bound('cache')) {
            $cacheKey = 'sushi-cache-' . static::class;
            app('cache')->forget($cacheKey);
        }
    }

    /**
     * Refresh theme data by clearing cache.
     */
    public static function refreshThemes(): void
    {
        static::clearCache();
    }
}