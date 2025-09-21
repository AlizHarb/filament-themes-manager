<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Commands;

use Alizharb\FilamentThemesManager\Services\ThemeManagerService;
use Alizharb\FilamentThemesManager\Models\Theme;
use Illuminate\Console\Command;

class ThemeDebugCommand extends Command
{
    protected $signature = 'theme:debug {slug? : Theme slug to debug}';
    protected $description = 'Debug theme deletion issues';

    public function handle(ThemeManagerService $service): int
    {
        $slug = $this->argument('slug');

        if ($slug) {
            return $this->debugSpecificTheme($service, $slug);
        }

        return $this->debugAllThemes($service);
    }

    protected function debugSpecificTheme(ThemeManagerService $service, string $slug): int
    {
        $this->info("Debugging theme: {$slug}");

        $theme = Theme::find($slug);
        if (!$theme) {
            $this->error("Theme not found: {$slug}");
            return 1;
        }

        $this->table(['Property', 'Value'], [
            ['Slug', $theme->slug],
            ['Name', $theme->name],
            ['Active', $theme->active ? 'Yes' : 'No'],
            ['Valid', $theme->is_valid ? 'Yes' : 'No'],
            ['Path', $theme->path],
            ['Path Exists', file_exists($theme->path) ? 'Yes' : 'No'],
            ['Is Protected', $theme->isProtected() ? 'Yes' : 'No'],
        ]);

        $activeTheme = $service->getActiveTheme();
        $this->info("Active theme: " . ($activeTheme ? $activeTheme->slug : 'None'));

        $protectedThemes = $service->getProtectedThemes();
        $this->info("Protected themes: " . implode(', ', $protectedThemes));

        $canDelete = $service->canDelete($slug);
        $this->info("Can delete: " . ($canDelete ? 'Yes' : 'No'));

        if (!$canDelete) {
            $this->warn("Deletion blocked reasons:");
            if ($theme->isProtected()) {
                $this->line("- Theme is protected");
            }
            if ($activeTheme && $activeTheme->slug === $slug) {
                $this->line("- Theme is currently active");
            }
        }

        return 0;
    }

    protected function debugAllThemes(ThemeManagerService $service): int
    {
        $themes = Theme::all();
        $activeTheme = $service->getActiveTheme();
        $protectedThemes = $service->getProtectedThemes();

        $data = [];
        foreach ($themes as $theme) {
            $data[] = [
                $theme->slug,
                $theme->name,
                $theme->active ? 'Yes' : 'No',
                $theme->is_valid ? 'Yes' : 'No',
                $theme->isProtected() ? 'Yes' : 'No',
                $service->canDelete($theme->slug) ? 'Yes' : 'No',
                file_exists($theme->path) ? 'Yes' : 'No',
            ];
        }

        $this->table([
            'Slug',
            'Name',
            'Active',
            'Valid',
            'Protected',
            'Can Delete',
            'Path Exists'
        ], $data);

        $this->info("Active theme: " . ($activeTheme ? $activeTheme->slug : 'None'));
        $this->info("Protected themes: " . implode(', ', $protectedThemes));

        return 0;
    }
}