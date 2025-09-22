<?php

declare(strict_types=1);

namespace Alizharb\FilamentThemesManager\Pages;

use Alizharb\FilamentThemesManager\Models\Theme;
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;
use Filament\Schemas\Components\{Section, Grid};
use Filament\Forms\Components\{Select, TextInput, FileUpload, Textarea, Toggle};
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, ImageColumn, ToggleColumn, IconColumn};
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\{Filter, SelectFilter};
use Filament\Actions\{Action, ActionGroup, BulkAction, BulkActionGroup};
use Filament\Infolists\Components\{TextEntry, ImageEntry};
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use UnitEnum;
use BackedEnum;

class ThemeManager extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament-themes-manager::theme-manager';

    protected ThemeManagerService $service;

    public function boot(ThemeManagerService $service): void
    {
        $this->service = $service;
    }

    protected function getListeners(): array
    {
        return [
            'refreshTable' => '$refresh',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Theme::query())
            ->columns([
                ImageColumn::make('screenshot')
                    ->label('')
                    ->getStateUsing(fn (Theme $record) => $record->hasScreenshot() ? $record->getScreenshotUrl() : null)
                    ->defaultImageUrl('/images/theme-placeholder.png')
                    ->imageSize(60)
                    ->circular(false),

                TextColumn::make('name')
                    ->label(__('filament-themes-manager::theme.table.theme_name'))
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Theme $record) => $record->description),

                TextColumn::make('version')
                    ->label(__('filament-themes-manager::theme.table.version'))
                    ->sortable()
                    ->badge()
                    ->color('info'),

                IconColumn::make('active')
                    ->label(__('filament-themes-manager::theme.table.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->action(function (Theme $record) {
                        if (!$record->active && $record->is_valid) {
                            $this->handleActivate($record->slug);
                        } elseif ($record->active && !$record->isProtected()) {
                            $this->handleDeactivate($record->slug);
                        } else {
                            $message = $record->isProtected()
                                ? __('filament-themes-manager::theme.notifications.theme_protected')
                                : __('filament-themes-manager::theme.notifications.theme_cannot_be_deactivated');

                            Notification::make()
                                ->title($message)
                                ->warning()
                                ->send();
                        }
                    })
                    ->tooltip(function (Theme $record) {
                        if ($record->active) {
                            return $record->isProtected()
                                ? __('filament-themes-manager::theme.tooltips.protected_active')
                                : __('filament-themes-manager::theme.tooltips.click_to_deactivate');
                        }
                        return $record->is_valid
                            ? __('filament-themes-manager::theme.tooltips.click_to_activate')
                            : __('filament-themes-manager::theme.tooltips.invalid_theme');
                    }),

                IconColumn::make('is_valid')
                    ->label(__('filament-themes-manager::theme.table.validity'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Theme $record) => $record->is_valid
                        ? __('filament-themes-manager::theme.status.valid')
                        : (is_array($record->errors) ? implode(', ', $record->errors) : (string) $record->errors)),

                TextColumn::make('author')
                    ->label(__('filament-themes-manager::theme.table.author'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('path')
                    ->label(__('filament-themes-manager::theme.table.path'))
                    ->wrap()
                    ->extraAttributes(['class' => 'text-xs'])
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label(__('filament-themes-manager::theme.actions.view'))
                        ->icon('heroicon-o-eye')
                        ->modal()
                        ->modalHeading(fn (Theme $record) => __('filament-themes-manager::theme.actions.view_theme', ['name' => $record->name]))
                        ->schema($this->getViewSchema())
                        ->modalSubmitAction(false)
                        ->modalWidth('3xl'),

                    Action::make('activate')
                        ->label(__('filament-themes-manager::theme.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Theme $record) => !$record->active && $record->is_valid)
                        ->action(fn (Theme $record) => $this->handleActivate($record->slug)),

                    Action::make('preview')
                        ->label(__('filament-themes-manager::theme.actions.preview'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (Theme $record) => route('theme.preview', $record->slug), shouldOpenInNewTab: true)
                        ->visible(fn (Theme $record) => $record->is_valid && config('filament-themes-manager.preview.enabled', true)),

                    Action::make('clone')
                        ->label(__('filament-themes-manager::theme.actions.clone'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->schema($this->getCloneSchema())
                        ->action(fn (Theme $record, array $data) => $this->handleClone($record->slug, $data)),

                    Action::make('delete')
                        ->label(__('filament-themes-manager::theme.actions.delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Theme $record) => $this->canDeleteTheme($record))
                        ->action(fn (Theme $record) => $this->handleDelete($record->slug)),
                ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament-themes-manager::theme.filters.status'))
                    ->options([
                        'active' => __('filament-themes-manager::theme.status.active'),
                        'inactive' => __('filament-themes-manager::theme.status.inactive'),
                    ])
                    ->query(fn ($query, $data) => match($data['value'] ?? null) {
                        'active' => $query->where('active', true),
                        'inactive' => $query->where('active', false),
                        default => $query,
                    }),

                SelectFilter::make('validity')
                    ->label(__('filament-themes-manager::theme.filters.validity'))
                    ->options([
                        'valid' => __('filament-themes-manager::theme.status.valid'),
                        'invalid' => __('filament-themes-manager::theme.status.invalid'),
                    ])
                    ->query(fn ($query, $data) => match($data['value'] ?? null) {
                        'valid' => $query->where('is_valid', true),
                        'invalid' => $query->where('is_valid', false),
                        default => $query,
                    }),

                Filter::make('name')
                    ->label(__('filament-themes-manager::theme.filters.name'))
                    ->schema([
                        TextInput::make('name')
                            ->placeholder(__('filament-themes-manager::theme.filters.name_placeholder'))
                    ])
                    ->query(fn ($query, $data) => $data['name']
                        ? $query->where('name', 'like', "%{$data['name']}%")
                        : $query),
            ])
            ->headerActions([
                Action::make('install')
                    ->label(__('filament-themes-manager::theme.actions.install'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema($this->getInstallSchema())
                    ->action(fn (array $data) => $this->handleInstall($data)),

                Action::make('refresh')
                    ->label(__('filament-themes-manager::theme.actions.refresh'))
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn () => $this->handleRefresh()),

                Action::make('clear_cache')
                    ->label(__('filament-themes-manager::theme.actions.clear_cache'))
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->action(fn () => $this->handleClearCache()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label(__('filament-themes-manager::theme.actions.delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament-themes-manager::theme.actions.confirm_bulk_delete'))
                        ->modalDescription(__('filament-themes-manager::theme.actions.confirm_bulk_delete_description'))
                        ->modalSubmitActionLabel(__('filament-themes-manager::theme.actions.delete_selected'))
                        ->action(fn ($records) => $this->handleBulkDelete($records))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (Theme $record) => $this->canDeleteTheme($record));
    }

    protected function handleActivate(string $slug): bool
    {
        if ($this->service->setActiveTheme($slug)) {
            Theme::refreshThemes();

            $this->dispatch('$refresh');

            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_activated'))
                ->success()
                ->send();

            $this->dispatch('refreshTable');
            return true;
        }

        Notification::make()
            ->title(__('filament-themes-manager::theme.notifications.theme_activation_failed'))
            ->danger()
            ->send();

        return false;
    }

    protected function handleDeactivate(string $slug): bool
    {
        Notification::make()
            ->title(__('filament-themes-manager::theme.notifications.cannot_deactivate_active'))
            ->warning()
            ->body(__('filament-themes-manager::theme.notifications.cannot_deactivate_active_body'))
            ->send();

        return false;
    }

    protected function canDeleteTheme(Theme $record): bool
    {
        if ($record->active) {
            return false;
        }

        if ($record->isProtected()) {
            return false;
        }

        return true;
    }

    protected function canDeactivate(string $slug): bool
    {
        if ($this->service->canDisable($slug)) {
            return true;
        }

        Notification::make()
            ->title(__('filament-themes-manager::theme.notifications.theme_cannot_be_deactivated'))
            ->warning()
            ->send();

        return false;
    }

    protected function handleClone(string $sourceSlug, array $data): void
    {
        if ($this->service->cloneTheme($sourceSlug, $data['slug'], $data['name'])) {
            Theme::refreshThemes();

            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_cloned'))
                ->success()
                ->send();

            $this->dispatch('refreshTable');
        } else {
            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_clone_failed'))
                ->danger()
                ->send();
        }
    }


    protected function handleInstall(array $data): void
    {
        $success = false;
        $source = $data['source'] ?? 'zip';

        if ($source === 'zip' && isset($data['zip'])) {
            $success = $this->service->installThemeFromZip($data['zip']);
        } elseif ($source === 'github' && !empty($data['github'])) {
            $success = $this->service->installThemeFromGitHub($data['github']);
        }

        if ($success) {
            Theme::refreshThemes();

            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_installed'))
                ->success()
                ->send();

            $this->dispatch('refreshTable');
        } else {
            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_installation_failed'))
                ->danger()
                ->send();
        }
    }

    protected function handleDelete(string $slug): void
    {
        try {
            // Check if theme can be deleted
            if (!$this->service->canDelete($slug)) {
                Notification::make()
                    ->title(__('filament-themes-manager::theme.notifications.theme_cannot_be_deleted'))
                    ->body(__('filament-themes-manager::theme.notifications.theme_protected_or_active'))
                    ->warning()
                    ->send();
                return;
            }

            if ($this->service->deleteTheme($slug)) {
                Theme::refreshThemes();

                Notification::make()
                    ->title(__('filament-themes-manager::theme.notifications.theme_deleted'))
                    ->body(__('filament-themes-manager::theme.notifications.theme_deleted_successfully', ['slug' => $slug]))
                    ->success()
                    ->send();

                $this->dispatch('refreshTable');
            } else {
                Notification::make()
                    ->title(__('filament-themes-manager::theme.notifications.theme_deletion_failed'))
                    ->body(__('filament-themes-manager::theme.notifications.theme_deletion_error', ['slug' => $slug]))
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.theme_deletion_failed'))
                ->body(__('filament-themes-manager::theme.notifications.unexpected_error', ['error' => $e->getMessage()]))
                ->danger()
                ->send();
        }
    }

    protected function handleBulkDelete(\Illuminate\Support\Collection $records, \Alizharb\FilamentThemesManager\Models\Theme $theme): void
    {
        $deletedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $errors = [];
        $skippedReasons = [];

        foreach ($records as $record) {
            try {
                // Get detailed information about why theme can't be deleted
                $activeTheme = $this->service->getActiveTheme();
                $isActive = $activeTheme && $activeTheme->slug === $record->slug;
                $isProtected = $this->service->getProtectedThemes();
                $isProtectedTheme = in_array($record->slug, $isProtected, true);

                // Check if theme can be deleted with detailed reason
                if (!$this->service->canDelete($record->slug)) {
                    $skippedCount++;

                    // Determine the specific reason
                    if ($isActive) {
                        $skippedReasons[] = $record->slug . ' (active theme)';
                    } elseif ($isProtectedTheme) {
                        $skippedReasons[] = $record->slug . ' (protected)';
                    } else {
                        $skippedReasons[] = $record->slug . ' (unknown reason)';
                    }
                    continue;
                }

                if ($this->service->deleteTheme($record->slug)) {
                    $deletedCount++;
                } else {
                    $errorCount++;
                    $errors[] = $record->slug . ' (deletion failed)';
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = $record->slug . ' (exception: ' . $e->getMessage() . ')';
            }
        }

        // Refresh themes if any were deleted
        if ($deletedCount > 0) {
            Theme::refreshThemes();
            $this->dispatch('refreshTable');
        }

        // Show appropriate notification based on results
        if ($deletedCount > 0 && $errorCount === 0 && $skippedCount === 0) {
            // All themes deleted successfully
            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.themes_deleted', ['count' => $deletedCount]))
                ->success()
                ->send();
        } elseif ($deletedCount > 0) {
            // Some themes deleted, some had issues
            $message = __('filament-themes-manager::theme.notifications.bulk_delete_partial', [
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount
            ]);

            $details = [];
            if (!empty($skippedReasons)) {
                $details[] = 'Skipped: ' . implode(', ', $skippedReasons);
            }
            if (!empty($errors)) {
                $details[] = 'Errors: ' . implode(', ', $errors);
            }

            if (!empty($details)) {
                $message .= ' | ' . implode(' | ', $details);
            }

            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.bulk_delete_completed'))
                ->body($message)
                ->warning()
                ->send();
        } else {
            // No themes were deleted
            $allDetails = array_merge($skippedReasons, $errors);
            $detailMessage = !empty($allDetails) ? 'Details: ' . implode(', ', $allDetails) : '';

            Notification::make()
                ->title(__('filament-themes-manager::theme.notifications.no_themes_deleted'))
                ->body(__('filament-themes-manager::theme.notifications.all_themes_protected_or_errors') . ' ' . $detailMessage)
                ->danger()
                ->send();
        }
    }

    protected function handleRefresh(): void
    {
        $this->service->clearCache();
        Theme::refreshThemes();

        Notification::make()
            ->title(__('filament-themes-manager::theme.notifications.themes_refreshed'))
            ->success()
            ->send();

        $this->dispatch('refreshTable');
    }

    protected function handleClearCache(): void
    {
        $this->service->clearCache();
        Theme::refreshThemes();

        Notification::make()
            ->title(__('filament-themes-manager::theme.notifications.cache_cleared'))
            ->success()
            ->send();
    }

    private function getViewSchema(): array
    {
        return [
            Section::make(__('filament-themes-manager::theme.sections.basic_info'))
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->icon('heroicon-o-tag')
                            ->label(__('filament-themes-manager::theme.fields.name')),
                        TextEntry::make('version')
                            ->icon('heroicon-o-tag')
                            ->label(__('filament-themes-manager::theme.fields.version')),
                        TextEntry::make('author')
                            ->icon('heroicon-o-user')
                            ->label(__('filament-themes-manager::theme.fields.author')),
                        TextEntry::make('license')
                            ->icon('heroicon-o-lock-closed')
                            ->label(__('filament-themes-manager::theme.fields.license')),
                    ]),
                    TextEntry::make('description')
                        ->icon('heroicon-o-document-text')
                        ->label(__('filament-themes-manager::theme.fields.description')),
                    TextEntry::make('homepage')
                        ->icon('heroicon-o-home')
                        ->label(__('filament-themes-manager::theme.fields.homepage'))
                        ->url(fn ($state) => $state, shouldOpenInNewTab: true),
                ])
                ->collapsible(),

            Section::make(__('filament-themes-manager::theme.sections.technical_info'))
                ->icon('heroicon-o-cpu-chip')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('slug')
                            ->icon('heroicon-o-tag')
                            ->label(__('filament-themes-manager::theme.fields.slug')),
                        TextEntry::make('parent')
                            ->icon('heroicon-o-tag')
                            ->label(__('filament-themes-manager::theme.fields.parent')),
                    ]),
                    TextEntry::make('path')
                        ->icon('heroicon-o-folder')
                        ->label(__('filament-themes-manager::theme.fields.path')),
                    TextEntry::make('requirements')
                        ->icon('heroicon-o-cpu-chip')
                        ->label(__('filament-themes-manager::theme.fields.requirements'))
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT)),
                    TextEntry::make('supports')
                        ->icon('heroicon-o-cpu-chip')
                        ->label(__('filament-themes-manager::theme.fields.supports'))
                        ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : (string) $state),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make(__('filament-themes-manager::theme.sections.screenshot'))
                ->icon('heroicon-o-photo')
                ->schema([
                    ImageEntry::make('screenshot')
                        ->label('')
                        ->getStateUsing(fn (Theme $record) => $record->hasScreenshot() ? $record->getScreenshotUrl() : null)
                        ->hiddenLabel(),
                ])
                ->visible(fn (Theme $record) => $record->hasScreenshot())
                ->collapsible()
                ->collapsed(),
        ];
    }

    private function getCloneSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('filament-themes-manager::theme.fields.new_name'))
                ->required()
                ->maxLength(50),

            TextInput::make('slug')
                ->label(__('filament-themes-manager::theme.fields.new_slug'))
                ->required()
                ->alphaDash()
                ->maxLength(50)
                ->unique(Theme::class, 'slug'),
        ];
    }


    private function getInstallSchema(): array
    {
        return [
            Select::make('source')
                ->label(__('filament-themes-manager::theme.fields.source'))
                ->options([
                    'zip' => __('filament-themes-manager::theme.sources.zip_file'),
                    'github' => __('filament-themes-manager::theme.sources.github'),
                ])
                ->default('zip')
                ->live()
                ->required(),

            FileUpload::make('zip')
                ->label(__('filament-themes-manager::theme.fields.zip_file'))
                ->acceptedFileTypes(['application/zip'])
                ->maxSize(config('filament-themes-manager.upload.max_size', (50 * 1024 * 1024)))
                ->disk(config('filament-themes-manager.upload.disk', 'public'))
                ->directory(config('filament-themes-manager.upload.directory', 'themes/uploads'))
                ->visible(fn ($get) => $get('source') === 'zip'),

            TextInput::make('github')
                ->label(__('filament-themes-manager::theme.fields.github'))
                ->placeholder('username/repository or https://github.com/username/repository')
                ->visible(fn ($get) => $get('source') === 'github'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        if (! config('filament-themes-manager.widgets.enabled', true)) {
            return [];
        }

        if (! config('filament-themes-manager.widgets.page', true)) {
            return [];
        }

        return [
            \Alizharb\FilamentThemesManager\Widgets\ThemesOverview::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-themes-manager.navigation.register', true);
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-themes-manager.navigation.sort', 200);
    }

    public static function getNavigationIcon(): string | BackedEnum | null
    {
        return config('filament-themes-manager.navigation.icon', 'heroicon-o-paint-brush');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('filament-themes-manager.navigation.group', 'System'));
    }

    public static function getNavigationLabel(): string
    {
        return __(config('filament-themes-manager.navigation.label', 'filament-themes-manager::theme.navigation.label'));
    }
}
