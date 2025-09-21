<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Data\ThemeData;

describe('ThemeData', function () {
    beforeEach(function () {
        $this->themeData = new ThemeData(
            name: 'Test Theme',
            slug: 'test-theme',
            description: 'A test theme',
            version: '1.0.0',
            author: 'Test Author',
            authorEmail: 'test@example.com',
            homepage: 'https://example.com',
            path: '/path/to/theme',
            active: true,
            parent: null,
            requirements: ['php' => '8.2', 'laravel' => '11.0'],
            assets: ['css/app.css', 'js/app.js'],
            supports: ['dark-mode', 'responsive'],
            license: 'MIT',
            screenshot: 'screenshot.png',
            isValid: true,
            errors: [],
            metadata: ['size' => 1024],
        );
    });

    it('can be instantiated with all properties', function () {
        expect($this->themeData->name)->toBe('Test Theme')
            ->and($this->themeData->slug)->toBe('test-theme')
            ->and($this->themeData->description)->toBe('A test theme')
            ->and($this->themeData->version)->toBe('1.0.0')
            ->and($this->themeData->author)->toBe('Test Author')
            ->and($this->themeData->authorEmail)->toBe('test@example.com')
            ->and($this->themeData->homepage)->toBe('https://example.com')
            ->and($this->themeData->path)->toBe('/path/to/theme')
            ->and($this->themeData->active)->toBeTrue()
            ->and($this->themeData->parent)->toBeNull()
            ->and($this->themeData->requirements)->toBe(['php' => '8.2', 'laravel' => '11.0'])
            ->and($this->themeData->assets)->toBe(['css/app.css', 'js/app.js'])
            ->and($this->themeData->supports)->toBe(['dark-mode', 'responsive'])
            ->and($this->themeData->license)->toBe('MIT')
            ->and($this->themeData->screenshot)->toBe('screenshot.png')
            ->and($this->themeData->isValid)->toBeTrue()
            ->and($this->themeData->errors)->toBe([])
            ->and($this->themeData->metadata)->toBe(['size' => 1024]);
    });

    it('can convert to array', function () {
        $array = $this->themeData->toArray();

        expect($array)
            ->toBeArray()
            ->toHaveKey('name', 'Test Theme')
            ->toHaveKey('slug', 'test-theme')
            ->toHaveKey('description', 'A test theme')
            ->toHaveKey('version', '1.0.0')
            ->toHaveKey('author', 'Test Author')
            ->toHaveKey('author_email', 'test@example.com')
            ->toHaveKey('homepage', 'https://example.com')
            ->toHaveKey('path', '/path/to/theme')
            ->toHaveKey('active', true)
            ->toHaveKey('parent', null)
            ->toHaveKey('requirements', ['php' => '8.2', 'laravel' => '11.0'])
            ->toHaveKey('assets', ['css/app.css', 'js/app.js'])
            ->toHaveKey('supports', ['dark-mode', 'responsive'])
            ->toHaveKey('license', 'MIT')
            ->toHaveKey('screenshot', 'screenshot.png')
            ->toHaveKey('is_valid', true)
            ->toHaveKey('errors', [])
            ->toHaveKey('metadata', ['size' => 1024]);
    });

    it('can check if has errors', function () {
        $themeWithoutErrors = new ThemeData(
            name: 'Valid Theme',
            slug: 'valid-theme',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        $themeWithErrors = new ThemeData(
            name: 'Invalid Theme',
            slug: 'invalid-theme',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: false,
            errors: ['Missing required field: name'],
            metadata: [],
        );

        expect($themeWithoutErrors->hasErrors())->toBeFalse()
            ->and($themeWithErrors->hasErrors())->toBeTrue();
    });

    it('can check if is protected', function () {
        config()->set('filament-themes-manager.security.protected_themes', ['default', 'test-theme']);

        expect($this->themeData->isProtected())->toBeTrue();

        $unprotectedTheme = new ThemeData(
            name: 'Unprotected Theme',
            slug: 'unprotected-theme',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($unprotectedTheme->isProtected())->toBeFalse();
    });

    it('can check if has screenshot', function () {
        // Create a temporary file for testing
        $tempPath = sys_get_temp_dir() . '/test_theme_' . uniqid();
        mkdir($tempPath, 0755, true);
        file_put_contents($tempPath . '/screenshot.png', 'fake image content');

        $themeWithScreenshot = new ThemeData(
            name: 'Theme With Screenshot',
            slug: 'theme-with-screenshot',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: $tempPath,
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: 'screenshot.png',
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithScreenshot->hasScreenshot())->toBeTrue();

        $themeWithoutScreenshot = new ThemeData(
            name: 'Theme Without Screenshot',
            slug: 'theme-without-screenshot',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithoutScreenshot->hasScreenshot())->toBeFalse();

        // Cleanup
        unlink($tempPath . '/screenshot.png');
        rmdir($tempPath);
    });

    it('can get screenshot path', function () {
        expect($this->themeData->getScreenshotPath())
            ->toBe('/path/to/theme' . DIRECTORY_SEPARATOR . 'screenshot.png');

        $themeWithoutScreenshot = new ThemeData(
            name: 'No Screenshot',
            slug: 'no-screenshot',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: [],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithoutScreenshot->getScreenshotPath())->toBeNull();
    });

    it('can check if supports feature', function () {
        expect($this->themeData->supportsFeature('dark-mode'))->toBeTrue()
            ->and($this->themeData->supportsFeature('responsive'))->toBeTrue()
            ->and($this->themeData->supportsFeature('non-existent-feature'))->toBeFalse();
    });

    it('can check PHP requirement', function () {
        expect($this->themeData->meetsPHPRequirement())->toBeTrue();

        $themeWithHigherPHPRequirement = new ThemeData(
            name: 'High PHP Requirement',
            slug: 'high-php',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: ['php' => '9.0'],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithHigherPHPRequirement->meetsPHPRequirement())->toBeFalse();
    });

    it('can check Laravel requirement', function () {
        expect($this->themeData->meetsLaravelRequirement())->toBeTrue();

        $themeWithHigherLaravelRequirement = new ThemeData(
            name: 'High Laravel Requirement',
            slug: 'high-laravel',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: ['laravel' => '20.0'],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithHigherLaravelRequirement->meetsLaravelRequirement())->toBeFalse();
    });

    it('can check all requirements', function () {
        expect($this->themeData->meetsAllRequirements())->toBeTrue();

        $themeWithUnmetRequirements = new ThemeData(
            name: 'Unmet Requirements',
            slug: 'unmet-requirements',
            description: null,
            version: '1.0.0',
            author: null,
            authorEmail: null,
            homepage: null,
            path: '/path',
            active: false,
            parent: null,
            requirements: ['php' => '9.0', 'laravel' => '20.0'],
            assets: [],
            supports: [],
            license: null,
            screenshot: null,
            isValid: true,
            errors: [],
            metadata: [],
        );

        expect($themeWithUnmetRequirements->meetsAllRequirements())->toBeFalse();
    });
});