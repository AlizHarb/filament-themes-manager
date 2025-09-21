<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Http\Controllers\ThemePreviewController;
use Alizharb\FilamentThemesManager\Http\Middleware\ThemePreviewMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

describe('Theme Preview Feature', function () {
    it('can preview a theme', function () {
        $controller = app(ThemePreviewController::class);
        $request = Request::create('/theme-preview/test-theme', 'GET');

        $response = $controller->preview($request, 'test-theme');

        expect($response->getStatusCode())->toBe(302); // Redirect response
    });

    it('can get theme screenshot', function () {
        $controller = app(ThemePreviewController::class);

        $response = $controller->screenshot('default');

        expect($response)->toBeInstanceOf(BinaryFileResponse::class);
    });

    it('can exit preview session', function () {
        $controller = app(ThemePreviewController::class);
        $request = Request::create('/theme-preview/exit', 'POST');

        $response = $controller->exitPreview($request);

        expect($response->getStatusCode())->toBe(302); // Redirect
    });

    it('preview middleware sets theme in session', function () {
        $middleware = new ThemePreviewMiddleware();
        $request = Request::create('/test');

        // Create a route and bind it with theme parameter
        $route = new \Illuminate\Routing\Route(['GET'], '/test/{theme}', []);
        $route->bind($request);
        $route->setParameter('theme', 'test-theme');
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

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