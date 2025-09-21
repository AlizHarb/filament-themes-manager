<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Commands;

use Illuminate\Console\Command;
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;

class ThemeInstallCommand extends Command
{
    protected $signature = 'theme:install
                            {source : The source of the theme (zip file path or GitHub repository)}
                            {--type=auto : Installation type (zip, github, auto)}
                            {--activate : Activate the theme after installation}';

    protected $description = 'Install a theme from ZIP file or GitHub repository';

    public function handle(ThemeManagerService $service): int
    {
        $source = $this->argument('source');
        $type = $this->option('type');
        $activate = $this->option('activate');

        if ($type === 'auto') {
            $type = $this->detectSourceType($source);
        }

        $this->info("Installing theme from {$type} source: {$source}");

        $success = false;

        if ($type === 'zip') {
            if (!file_exists($source)) {
                $this->error("❌ ZIP file not found: {$source}");
                return Command::FAILURE;
            }
            $success = $service->installThemeFromZip($source);
        } elseif ($type === 'github') {
            $success = $service->installThemeFromGitHub($source);
        } else {
            $this->error("❌ Unsupported installation type: {$type}");
            return Command::FAILURE;
        }

        if ($success) {
            $this->info("✅ Theme installed successfully!");

            if ($activate) {
                // Extract theme slug from installation and activate
                $themes = $service->getAllThemes();
                $latestTheme = $themes->sortByDesc('metadata.last_modified')->first();

                if ($latestTheme && $service->setActiveTheme($latestTheme->slug)) {
                    $this->info("✅ Theme '{$latestTheme->name}' activated!");
                } else {
                    $this->warn("⚠️ Theme installed but activation failed");
                }
            }

            return Command::SUCCESS;
        }

        $this->error("❌ Failed to install theme");
        return Command::FAILURE;
    }

    protected function detectSourceType(string $source): string
    {
        if (str_ends_with(strtolower($source), '.zip')) {
            return 'zip';
        }

        if (str_contains($source, 'github.com') || str_contains($source, '/')) {
            return 'github';
        }

        return 'unknown';
    }
}