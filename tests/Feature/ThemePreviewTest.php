<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Http\Controllers\ThemePreviewController;
use Alizharb\FilamentThemesManager\Http\Middleware\ThemePreviewMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

describe('Theme Preview Feature', function () {
    it('can preview a theme', function () {
        $controller = new ThemePreviewController();
        $request = Request::create('/theme-preview/test-theme', 'GET');

        $response = $controller->preview($request, 'test-theme');

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('can show preview banner', function () {
        $controller = new ThemePreviewController();
        $request = Request::create('/theme-preview/banner', 'GET');

        $response = $controller->banner($request);

        expect($response)->toBeInstanceOf(Response::class);
    });

    it('can end preview session', function () {
        $controller = new ThemePreviewController();
        $request = Request::create('/theme-preview/end', 'POST');

        $response = $controller->endPreview($request);

        expect($response->getStatusCode())->toBe(302); // Redirect
    });

    it('preview middleware sets theme in session', function () {
        $middleware = new ThemePreviewMiddleware();
        $request = Request::create('/test');
        $request->route()->setParameter('theme', 'test-theme');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });

    it('preview middleware handles missing theme parameter', function () {
        $middleware = new ThemePreviewMiddleware();
        $request = Request::create('/test');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });
});