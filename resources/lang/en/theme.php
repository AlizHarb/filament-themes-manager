<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Theme Manager',
        'group' => 'System',
    ],

    'table' => [
        'theme_name' => 'Theme Name',
        'version' => 'Version',
        'status' => 'Status',
        'validity' => 'Valid',
        'author' => 'Author',
        'path' => 'Path',
    ],

    'actions' => [
        'view' => 'View Details',
        'view_theme' => 'Theme Details: :name',
        'activate' => 'Activate',
        'preview' => 'Preview',
        'clone' => 'Clone Theme',
        'delete' => 'Delete',
        'install' => 'Install Theme',
        'refresh' => 'Refresh',
        'clear_cache' => 'Clear Cache',
        'confirm_bulk_delete' => 'Delete Selected Themes',
        'confirm_bulk_delete_description' => 'Are you sure you want to delete the selected themes? This action cannot be undone. Protected and active themes will be skipped.',
        'delete_selected' => 'Delete Selected',
    ],

    'filters' => [
        'status' => 'Status',
        'validity' => 'Validity',
        'name' => 'Theme Name',
        'name_placeholder' => 'Search by theme name...',
    ],

    'fields' => [
        'name' => 'Theme Name',
        'slug' => 'Theme Slug',
        'slug_help' => 'Auto-generated from theme name. You can edit if needed.',
        'new_name' => 'New Theme Name',
        'new_slug' => 'New Theme Slug',
        'description' => 'Description',
        'version' => 'Version',
        'author' => 'Author',
        'author_email' => 'Author Email',
        'homepage' => 'Homepage',
        'license' => 'License',
        'parent' => 'Parent Theme',
        'parent_theme' => 'Parent Theme (Optional)',
        'requirements' => 'Requirements',
        'supports' => 'Supported Features',
        'path' => 'Path',
        'source' => 'Installation Source',
        'zip_file' => 'ZIP File',
        'github' => 'GitHub Repository',
    ],

    'sources' => [
        'zip_file' => 'Upload ZIP File',
        'github' => 'GitHub Repository',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
        'technical_info' => 'Technical Information',
        'screenshot' => 'Screenshot',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'valid' => 'Valid',
        'invalid' => 'Invalid',
    ],

    'stats' => [
        'total_themes' => 'Total Themes',
        'total_themes_description' => 'Themes installed',
        'active_theme' => 'Active Theme',
        'active_theme_description' => 'Currently active',
        'valid_themes' => 'Valid Themes',
        'valid_themes_description' => 'Properly configured',
        'invalid_themes' => 'Invalid Themes',
        'invalid_themes_description' => 'Have errors',
    ],

    'notifications' => [
        'theme_activated' => 'Theme activated successfully',
        'theme_activation_failed' => 'Failed to activate theme',
        'theme_cannot_be_deactivated' => 'This theme cannot be deactivated',
        'theme_protected' => 'This theme is protected and cannot be modified',
        'cannot_deactivate_active' => 'Cannot deactivate active theme',
        'cannot_deactivate_active_body' => 'There must always be an active theme. Switch to another theme first.',
        'theme_cloned' => 'Theme cloned successfully',
        'theme_clone_failed' => 'Failed to clone theme',
        'theme_installed' => 'Theme installed successfully',
        'theme_installation_failed' => 'Failed to install theme',
        'theme_deleted' => 'Theme deleted successfully',
        'theme_deletion_failed' => 'Failed to delete theme',
        'theme_cannot_be_deleted' => 'Theme cannot be deleted',
        'theme_protected_or_active' => 'This theme is either protected or currently active and cannot be deleted.',
        'theme_deleted_successfully' => 'Theme ":slug" has been deleted successfully.',
        'theme_deletion_error' => 'An error occurred while deleting theme ":slug".',
        'unexpected_error' => 'An unexpected error occurred: :error',
        'themes_deleted' => ':count themes deleted successfully',
        'bulk_delete_partial' => 'Bulk deletion completed: :deleted deleted, :skipped skipped, :errors errors.',
        'bulk_delete_completed' => 'Bulk deletion completed with some issues',
        'failed_themes' => 'Failed themes',
        'no_themes_deleted' => 'No themes were deleted',
        'all_themes_protected_or_errors' => 'All selected themes are either protected, active, or encountered errors.',
        'themes_refreshed' => 'Themes list refreshed',
        'cache_cleared' => 'Theme cache cleared successfully',
    ],

    'tooltips' => [
        'protected_active' => 'This theme is protected and currently active',
        'click_to_deactivate' => 'Click to deactivate this theme',
        'click_to_activate' => 'Click to activate this theme',
        'invalid_theme' => 'This theme has errors and cannot be activated',
    ],

    'errors' => [
        'theme_not_found' => 'Theme not found',
        'invalid_theme_structure' => 'Invalid theme structure',
        'theme_already_exists' => 'Theme already exists',
        'cannot_delete_active_theme' => 'Cannot delete the active theme',
        'cannot_delete_protected_theme' => 'Cannot delete protected theme',
        'php_requirements_not_met' => 'PHP requirements not met',
        'laravel_requirements_not_met' => 'Laravel requirements not met',
    ],
];