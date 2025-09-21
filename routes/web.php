<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Alizharb\FilamentThemesManager\Http\Controllers\ThemePreviewController;

$routePrefix = config('filament-themes-manager.preview.route_prefix', 'theme-preview');

Route::middleware(['web'])
    ->prefix($routePrefix)
    ->name('theme.')
    ->group(function () {
        Route::get('/{slug}', [ThemePreviewController::class, 'preview'])
            ->name('preview')
            ->where('slug', '[a-z0-9\-_]+');

        Route::get('/exit', [ThemePreviewController::class, 'exitPreview'])
            ->name('preview.exit');

        Route::post('/{slug}/activate', [ThemePreviewController::class, 'activate'])
            ->name('preview.activate')
            ->where('slug', '[a-z0-9\-_]+');

        Route::get('/{slug}/screenshot', [ThemePreviewController::class, 'screenshot'])
            ->name('screenshot')
            ->where('slug', '[a-z0-9\-_]+');
    });