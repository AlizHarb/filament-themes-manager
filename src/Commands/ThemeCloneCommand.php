<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Commands;

use Illuminate\Console\Command;
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;

class ThemeCloneCommand extends Command
{
    protected $signature = 'theme:clone
                            {source : The slug of the theme to clone}
                            {name : The name for the new theme}
                            {--slug= : The slug for the new theme (optional)}
                            {--activate : Activate the cloned theme}';

    protected $description = 'Clone an existing theme';

    public function handle(ThemeManagerService $service): int
    {
        $sourceSlug = $this->argument('source');
        $name = $this->argument('name');
        $slug = $this->option('slug') ?? str($name)->slug();
        $activate = $this->option('activate');

        $this->info("Cloning theme '{$sourceSlug}' to '{$name}'");

        if ($service->cloneTheme($sourceSlug, $slug, $name)) {
            $this->info("✅ Theme cloned successfully!");
            $this->info("📁 New theme location: " . resource_path("themes/{$slug}"));

            if ($activate) {
                if ($service->setActiveTheme($slug)) {
                    $this->info("✅ Theme '{$name}' activated!");
                } else {
                    $this->warn("⚠️ Theme cloned but activation failed");
                }
            }

            $this->newLine();
            $this->info("The cloned theme inherits from the source theme.");
            $this->info("You can now customize it in the themes directory.");

            return Command::SUCCESS;
        }

        $this->error("❌ Failed to clone theme");
        $this->error("Source theme might not exist or target theme already exists.");

        return Command::FAILURE;
    }
}