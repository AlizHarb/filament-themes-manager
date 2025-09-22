<?php

declare(strict_types=1);

use Alizharb\FilamentThemesManager\Http\Controllers\ThemePreviewController;
use Alizharb\FilamentThemesManager\Http\Middleware\ThemePreviewMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
<<<<<<< HEAD
use Symfony\Component\HttpFoundation\BinaryFileResponse;

describe('Theme Preview Feature', function () {
    it('can preview a theme', function () {
        $controller = app(ThemePreviewController::class);
=======

describe('Theme Preview Feature', function () {
    it('can preview a theme', function () {
        $controller = new ThemePreviewController();
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc
        $request = Request::create('/theme-preview/test-theme', 'GET');

        $response = $controller->preview($request, 'test-theme');

<<<<<<< HEAD
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
=======
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
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc

        expect($response->getStatusCode())->toBe(302); // Redirect
    });

    it('preview middleware sets theme in session', function () {
        $middleware = new ThemePreviewMiddleware();
        $request = Request::create('/test');
<<<<<<< HEAD

        // Create a route and bind it with theme parameter
        $route = new \Illuminate\Routing\Route(['GET'], '/test/{theme}', []);
        $route->bind($request);
        $route->setParameter('theme', 'test-theme');
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });
=======
        $request->route()->setParameter('theme', 'test-theme');
>>>>>>> ea01d2758692da0be7cd5c527eadfdf7938c7ebc

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