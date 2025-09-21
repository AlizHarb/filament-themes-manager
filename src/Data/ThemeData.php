<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Data;

class ThemeData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $version,
        public readonly ?string $author,
        public readonly ?string $authorEmail,
        public readonly ?string $homepage,
        public readonly string $path,
        public readonly bool $active,
        public readonly ?string $parent,
        public readonly array $requirements,
        public readonly array $assets,
        public readonly array $supports,
        public readonly ?string $license,
        public readonly ?string $screenshot,
        public readonly bool $isValid,
        public readonly array $errors = [],
        public readonly array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'version' => $this->version,
            'author' => $this->author,
            'author_email' => $this->authorEmail,
            'homepage' => $this->homepage,
            'path' => $this->path,
            'active' => $this->active,
            'parent' => $this->parent,
            'requirements' => $this->requirements,
            'assets' => $this->assets,
            'supports' => $this->supports,
            'license' => $this->license,
            'screenshot' => $this->screenshot,
            'is_valid' => $this->isValid,
            'errors' => $this->errors,
            'metadata' => $this->metadata,
        ];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function isProtected(): bool
    {
        return in_array($this->slug, config('filament-themes-manager.security.protected_themes', []));
    }

    public function hasScreenshot(): bool
    {
        return !empty($this->screenshot) && file_exists($this->getScreenshotPath());
    }

    public function getScreenshotPath(): ?string
    {
        if (!$this->screenshot) {
            return null;
        }

        return $this->path . DIRECTORY_SEPARATOR . $this->screenshot;
    }

    public function getScreenshotUrl(): ?string
    {
        if (!$this->hasScreenshot()) {
            return null;
        }

        $relativePath = str_replace(base_path(), '', $this->getScreenshotPath());
        return asset(ltrim($relativePath, DIRECTORY_SEPARATOR));
    }

    public function supportsFeature(string $feature): bool
    {
        return in_array($feature, $this->supports);
    }

    public function meetsPHPRequirement(): bool
    {
        if (!isset($this->requirements['php'])) {
            return true;
        }

        return version_compare(PHP_VERSION, $this->requirements['php'], '>=');
    }

    public function meetsLaravelRequirement(): bool
    {
        if (!isset($this->requirements['laravel'])) {
            return true;
        }

        $laravelVersion = app()->version();
        return version_compare($laravelVersion, $this->requirements['laravel'], '>=');
    }

    public function meetsAllRequirements(): bool
    {
        return $this->meetsPHPRequirement() && $this->meetsLaravelRequirement();
    }
}