<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ThemePreviewMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if preview session has expired
        if (session()->has('theme_preview_expires') &&
            now()->isAfter(session('theme_preview_expires'))) {

            session()->forget(['theme_preview_original', 'theme_preview_expires']);

            // Restore original theme
            $originalTheme = session('theme_preview_original');
            if ($originalTheme) {
                \Qirolab\Theme\Theme::set($originalTheme);
            }
        }

        $response = $next($request);

        // Add theme preview banner to HTML responses
        if ($this->shouldAddPreviewBanner($request, $response)) {
            $content = $response->getContent();
            $banner = $this->getPreviewBanner();

            if ($banner && $content) {
                // Try to insert after opening body tag first
                if (preg_match('/(<body[^>]*>)/i', $content)) {
                    $content = preg_replace('/(<body[^>]*>)/i', '$1' . $banner, $content);
                } else {
                    // Fallback: insert at the beginning of the content
                    $content = $banner . $content;
                }
                $response->setContent($content);
            }
        }

        return $response;
    }

    protected function shouldAddPreviewBanner(Request $request, Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');

        return session()->has('theme_preview_original') &&
               (str_contains($contentType, 'text/html') || empty($contentType)) &&
               !$request->ajax() &&
               !$request->wantsJson() &&
               !$request->expectsJson() &&
               config('filament-themes-manager.preview.enabled', true);
    }

    protected function getPreviewBanner(): string
    {
        $previewData = session('theme_preview');

        if (!$previewData) {
            return '';
        }

        return View::make('filament-themes-manager::preview-banner', $previewData)->render();
    }
}