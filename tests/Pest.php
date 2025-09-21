<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');

expect()->extend('toBeValidTheme', function () {
    return $this
        ->toHaveKey('name')
        ->toHaveKey('slug')
        ->toHaveKey('version')
        ->toHaveKey('is_valid', true);
});

expect()->extend('toBeInvalidTheme', function () {
    return $this->toHaveKey('is_valid', false);
});

expect()->extend('toHaveErrors', function () {
    return $this
        ->toHaveKey('errors')
        ->and($this->value['errors'])->not->toBeEmpty();
});

/**
 * Helper function to create a temporary theme for testing
 */
function createTempTheme(string $name, array $config = []): string
{
    $tempPath = sys_get_temp_dir() . '/test_themes_' . uniqid();
    mkdir($tempPath, 0755, true);

    $themeConfig = array_merge([
        'name' => $name,
        'slug' => str_replace(' ', '-', strtolower($name)),
        'version' => '1.0.0',
        'description' => "Test theme: {$name}",
    ], $config);

    file_put_contents(
        $tempPath . '/theme.json',
        json_encode($themeConfig, JSON_PRETTY_PRINT)
    );

    // Create views directory
    mkdir($tempPath . '/views', 0755, true);
    file_put_contents(
        $tempPath . '/views/test.blade.php',
        '<div>Test view for {{ $theme ?? "' . $name . '" }}</div>'
    );

    return $tempPath;
}

/**
 * Helper function to clean up temporary themes
 */
function cleanupTempTheme(string $path): void
{
    if (file_exists($path)) {
        shell_exec("rm -rf " . escapeshellarg($path));
    }
}