<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Models\Theme;
use Alizharb\FilamentThemesManager\Data\ThemeData;

describe('Theme Model', function () {
    beforeEach(function () {
        Theme::clearCache();
    });

    it('can retrieve all themes', function () {
        $themes = Theme::all();

        expect($themes)->toHaveCount(3)
            ->and($themes->pluck('slug')->toArray())
            ->toContain('default', 'test-theme', 'invalid-theme');
    });

    it('can find theme by slug', function () {
        $theme = Theme::find('default');

        expect($theme)
            ->not->toBeNull()
            ->and($theme->slug)->toBe('default')
            ->and($theme->name)->toBe('Default Theme')
            ->and($theme->version)->toBe('1.0.0');
    });

    it('returns null for non-existent theme', function () {
        $theme = Theme::find('non-existent');

        expect($theme)->toBeNull();
    });

    it('can get active themes', function () {
        $activeThemes = Theme::active()->get();

        expect($activeThemes)->toHaveCount(1)
            ->and($activeThemes->first()->slug)->toBe('default');
    });

    it('can get inactive themes', function () {
        $inactiveThemes = Theme::inactive()->get();

        expect($inactiveThemes)->toHaveCount(2)
            ->and($inactiveThemes->pluck('slug')->toArray())
            ->toContain('test-theme', 'invalid-theme');
    });

    it('can search themes by name', function () {
        $themes = Theme::byName('Test')->get();

        expect($themes)->toHaveCount(1)
            ->and($themes->first()->name)->toBe('Test Theme');
    });

    it('can filter valid themes', function () {
        $validThemes = Theme::valid()->get();

        expect($validThemes)->toHaveCount(2)
            ->and($validThemes->pluck('slug')->toArray())
            ->toContain('default', 'test-theme')
            ->not->toContain('invalid-theme');
    });

    it('can filter invalid themes', function () {
        $invalidThemes = Theme::invalid()->get();

        expect($invalidThemes)->toHaveCount(1)
            ->and($invalidThemes->first()->slug)->toBe('invalid-theme');
    });

    it('correctly identifies protected themes', function () {
        $defaultTheme = Theme::find('default');
        $testTheme = Theme::find('test-theme');

        expect($defaultTheme->isProtected())->toBeTrue()
            ->and($testTheme->isProtected())->toBeFalse();
    });

    it('can check if theme has screenshot', function () {
        $defaultTheme = Theme::find('default');
        $testTheme = Theme::find('test-theme');

        expect($defaultTheme->hasScreenshot())->toBeTrue()
            ->and($testTheme->hasScreenshot())->toBeFalse();
    });

    it('can get screenshot url', function () {
        $theme = Theme::find('default');
        $url = $theme->getScreenshotUrl();

        expect($url)
            ->not->toBeNull()
            ->toContain('screenshot.png');
    });

    it('can convert to theme data', function () {
        $theme = Theme::find('default');
        $data = $theme->toData();

        expect($data)
            ->toBeInstanceOf(ThemeData::class)
            ->and($data->name)->toBe('Default Theme')
            ->and($data->slug)->toBe('default')
            ->and($data->isValid)->toBeTrue();
    });

    it('can get all themes as data collection', function () {
        $themesData = Theme::allData();

        expect($themesData)->toHaveCount(3)
            ->and($themesData->first())->toBeInstanceOf(ThemeData::class);
    });

    it('can find theme data by slug', function () {
        $themeData = Theme::findData('default');

        expect($themeData)
            ->toBeInstanceOf(ThemeData::class)
            ->and($themeData->slug)->toBe('default');
    });

    it('returns null when finding non-existent theme data', function () {
        $themeData = Theme::findData('non-existent');

        expect($themeData)->toBeNull();
    });

    it('can get theme counts', function () {
        expect(Theme::getActiveCount())->toBe(1)
            ->and(Theme::getInactiveCount())->toBe(2)
            ->and(Theme::getValidCount())->toBe(2)
            ->and(Theme::getInvalidCount())->toBe(1);
    });

    it('can check if theme exists by slug', function () {
        expect(Theme::existsBySlug('default'))->toBeTrue()
            ->and(Theme::existsBySlug('non-existent'))->toBeFalse();
    });

    it('validates theme structure correctly', function () {
        $validTheme = Theme::find('default');
        $invalidTheme = Theme::find('invalid-theme');

        expect($validTheme->is_valid)->toBeTrue()
            ->and($invalidTheme->is_valid)->toBeFalse()
            ->and($invalidTheme->errors)->not->toBeEmpty();
    });

    it('properly handles theme metadata', function () {
        $theme = Theme::find('default');

        expect($theme->metadata)
            ->toBeArray()
            ->toHaveKey('blade_files_count')
            ->toHaveKey('css_files_count')
            ->toHaveKey('js_files_count')
            ->toHaveKey('size')
            ->toHaveKey('last_modified');
    });

    it('correctly handles theme requirements', function () {
        $theme = Theme::find('default');

        expect($theme->requirements)
            ->toBeArray()
            ->toHaveKey('php', '8.2')
            ->toHaveKey('laravel', '11.0');
    });

    it('can clear cache', function () {
        // Load themes to populate cache
        Theme::all();

        expect(fn() => Theme::clearCache())->not->toThrow();
    });

    it('can refresh themes', function () {
        expect(fn() => Theme::refreshThemes())->not->toThrow();
    });
});