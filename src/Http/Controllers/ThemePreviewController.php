<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\{RedirectResponse, Response};
use Illuminate\Routing\Controller;
use Alizharb\FilamentThemesManager\Models\Theme;
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;
use Qirolab\Theme\Theme as ThemeFacade;

class ThemePreviewController extends Controller
{
    public function __construct(
        protected ThemeManagerService $service
    ) {}

    public function preview(Request $request, string $slug): RedirectResponse
    {
        if (!config('filament-themes-manager.preview.enabled', true)) {
            abort(404);
        }

        $theme = Theme::findData($slug);

        if (!$theme || !$theme->isValid) {
            abort(404, 'Theme not found or invalid');
        }

        // Validate requirements
        $errors = $this->service->validateThemeRequirements($slug);
        if (!empty($errors)) {
            abort(422, 'Theme requirements not met: ' . implode(', ', $errors));
        }

        // Store original theme in session for restoration
        session(['theme_preview_original' => ThemeFacade::active()]);
        session(['theme_preview_expires' => now()->addSeconds(config('filament-themes-manager.preview.session_duration', 3600))]);

        // Temporarily set the preview theme
        ThemeFacade::set($slug, $theme->parent);

        // Redirect to home page or requested URL with preview indicator
        $redirectUrl = $request->get('url', '/');

        return redirect($redirectUrl)
            ->with('theme_preview', [
                'slug' => $slug,
                'name' => $theme->name,
                'original' => session('theme_preview_original'),
            ]);
    }

    public function exitPreview(Request $request): RedirectResponse
    {
        $originalTheme = session('theme_preview_original');

        if ($originalTheme) {
            ThemeFacade::set($originalTheme);
        } else {
            ThemeFacade::clear();
        }

        // Clear preview session data
        session()->forget(['theme_preview_original', 'theme_preview_expires']);

        $redirectUrl = $request->get('url', '/');

        return redirect($redirectUrl)
            ->with('message', 'Theme preview ended. Returned to original theme.');
    }

    public function activate(Request $request, string $slug): RedirectResponse
    {
        $theme = Theme::findData($slug);

        if (!$theme || !$theme->isValid) {
            abort(404, 'Theme not found or invalid');
        }

        if ($this->service->setActiveTheme($slug)) {
            // Clear preview session data since we're now using this theme permanently
            session()->forget(['theme_preview_original', 'theme_preview_expires']);

            $redirectUrl = $request->get('url', '/');

            return redirect($redirectUrl)
                ->with('message', "Theme '{$theme->name}' has been activated successfully!");
        }

        abort(500, 'Failed to activate theme');
    }

    public function screenshot(string $slug): Response
    {
        $theme = Theme::findData($slug);

        if (!$theme || !$theme->hasScreenshot()) {
            abort(404);
        }

        $screenshotPath = $theme->getScreenshotPath();

        if (!file_exists($screenshotPath)) {
            abort(404);
        }

        return response()->file($screenshotPath);
    }
}
