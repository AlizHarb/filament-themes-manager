# Filament Themes Manager

<div align="center">

<img src="https://banners.beyondco.de/Filament%20Themes%20Manager.jpeg?theme=light&packageManager=composer+require&packageName=alizharb%2Ffilament-themes-manager&pattern=architect&style=style_1&description=Filament-powered+admin+panel+to+effortlessly+install%2C+preview%2C+and+manage+themes.&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg" alt="Filament Themes Manager">
    
[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/filament-themes-manager.svg?style=flat-square)](https://packagist.org/packages/alizharb/filament-themes-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/filament-themes-manager.svg?style=flat-square)](https://packagist.org/packages/alizharb/filament-themes-manager)
[![License](https://img.shields.io/packagist/l/alizharb/filament-themes-manager.svg?style=flat-square)](https://github.com/alizharb/filament-themes-manager/blob/main/LICENSE.md)
[![GitHub Stars](https://img.shields.io/github/stars/AlizHarb/filament-themes-manager.svg?style=style=flat-square&color=yellow)](https://github.com/AlizHarb/filament-themes-manager/stargazers)
[![PHP Version](https://img.shields.io/packagist/php-v/alizharb/filament-themes-manager.svg?style=style=flat-square&color=purple)](https://packagist.org/packages/alizharb/filament-themes-manager)

</div>

**A comprehensive, enterprise-grade theme management system for Laravel Filament applications.**

Built to seamlessly integrate with `qirolab/laravel-themer`, this package provides a sophisticated admin interface for managing, installing, previewing, and switching themes with professional-grade security and performance optimization.

---

## 🚀 Key Features

### Theme Management

- **Multi-source Installation** - Deploy themes from ZIP files, GitHub repositories, or local directories
- **Advanced Cloning System** - Duplicate and customize existing themes with intelligent inheritance
- **One-Click Activation** - Seamlessly switch between themes without downtime
- **Safe Deletion** - Remove themes with built-in protection for critical system themes
- **Live Preview** - Test themes in isolation without affecting other users

### Security & Validation

- **Structure Validation** - Comprehensive theme integrity checking
- **File Type Restrictions** - Configurable security policies for theme uploads
- **Protected Themes** - Safeguard critical themes from accidental deletion
- **Compatibility Checking** - Automatic PHP/Laravel version validation
- **Malicious Code Scanning** - Optional security scanning for theme files

### Developer Experience

- **Artisan CLI Integration** - Full command-line theme management
- **Comprehensive API** - Programmatic theme operations
- **Event System** - Custom integrations and workflows
- **Multi-language Support** - Extensive internationalization
- **Cache Optimization** - Performance-tuned with intelligent caching

### Analytics & Monitoring

- **Usage Analytics** - Detailed theme performance metrics
- **Health Monitoring** - Real-time validation status tracking
- **Statistics Dashboard** - Comprehensive overview widgets
- **Asset Compilation** - Build status and asset management

---

## 📋 System Requirements

| Component          | Version            |
| ------------------ | ------------------ |
| **PHP**            | `^8.2\|^8.3\|^8.4` |
| **Laravel**        | `^11.0\|^12.0`     |
| **Filament**       | `^4.0`             |
| **Laravel Themer** | `^2.0`             |

---

## ⚡ Quick Installation

### 1. Install Package

```bash
composer require alizharb/filament-themes-manager
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=filament-themes-manager-config
```

### 3. Register Plugin

Add to your Filament panel in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Alizharb\FilamentThemesManager\FilamentThemesManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentThemesManagerPlugin::make(),
        ]);
}
```

### 4. Enable Preview System (Optional)

Add middleware to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Alizharb\FilamentThemesManager\Http\Middleware\ThemePreviewMiddleware::class,
    ]);
})
```

---

## 🎯 Usage Guide

### Admin Interface

Access the theme management interface at **System → Theme Manager** in your Filament admin panel.

#### Core Operations

| Action             | Description                              | Access         |
| ------------------ | ---------------------------------------- | -------------- |
| **View Details**   | Inspect theme configuration and metadata | Eye icon       |
| **Activate Theme** | Switch active theme instantly            | Status toggle  |
| **Preview Theme**  | Test theme safely in session             | Preview button |
| **Clone Theme**    | Duplicate theme for customization        | Clone action   |
| **Delete Theme**   | Remove unused themes safely              | Delete button  |

#### Bulk Operations

- **Multi-select** themes using checkboxes
- **Bulk deletion** of compatible themes
- **Configuration export** for backup and migration

### Command Line Interface

#### GitHub Installation

```bash
php artisan theme:install username/repository --type=github --activate
```

#### ZIP Installation

```bash
php artisan theme:install /path/to/theme.zip --type=zip
```

#### Theme Cloning

```bash
php artisan theme:clone default "Custom Default" --slug=custom-default --activate
```

---

## 🏗️ Theme Structure

### Required Directory Layout

```
themes/my-theme/
├── theme.json          # Theme manifest (required)
├── views/              # Blade templates
│   ├── components/     # Reusable components
│   └── layouts/        # Page layouts
├── css/
│   └── app.css        # Stylesheets
├── js/
│   └── app.js         # JavaScript assets
├── vite.config.js     # Build configuration
└── screenshot.png     # Preview image
```

### Theme Manifest (theme.json)

```json
{
  "name": "Professional Theme",
  "slug": "professional-theme",
  "version": "1.0.0",
  "description": "A professional theme for Laravel applications",
  "author": "Your Name",
  "author_email": "contact@example.com",
  "homepage": "https://example.com",
  "screenshot": "screenshot.png",
  "parent": null,
  "requirements": {
    "php": ">=8.3",
    "laravel": ">=12.0"
  },
  "assets": ["css/app.css", "js/app.js"],
  "supports": ["responsive", "dark-mode"],
  "license": "MIT"
}
```

---

## ⚙️ Configuration

### Theme Discovery

```php
'discovery' => [
    'paths' => [
        'themes' => base_path('themes'),
        'resources' => resource_path('themes'),
    ],
    'cache_duration' => 3600,
    'auto_discover' => true,
],
```

### Security Configuration

```php
'security' => [
    'validate_theme_structure' => true,
    'scan_malicious_code' => true,
    'protected_themes' => ['default'],
    'allowed_file_types' => [
        'php', 'blade.php', 'css', 'scss', 'js', 'vue', 'json'
    ],
],
```

### Preview System

```php
'preview' => [
    'enabled' => true,
    'route_prefix' => 'theme-preview',
    'session_duration' => 3600,
    'cache_screenshots' => true,
],
```

### Dashboard Widgets

```php
'widgets' => [
    'enabled' => true,
    'page' => true,      // Theme Manager page
    'dashboard' => true, // Main dashboard
],
```

---

## 🎨 Live Preview System

### Capabilities

- **Session Isolation** - Preview affects only current user
- **Professional UI** - Elegant banner with intuitive controls
- **One-Click Activation** - Seamless transition to permanent theme
- **Auto-Expiration** - Automatic fallback to original theme
- **Keyboard Shortcuts** - `Ctrl+H` to toggle banner visibility

### Preview Routes

| Route                            | Purpose                |
| -------------------------------- | ---------------------- |
| `/theme-preview/{slug}`          | Activate preview       |
| `/theme-preview/exit`            | Exit preview mode      |
| `/theme-preview/{slug}/activate` | Make preview permanent |

---

## 🔧 API Reference

### Theme Manager Service

```php
use Alizharb\FilamentThemesManager\Services\ThemeManagerService;

$service = app(ThemeManagerService::class);

// Theme operations
$themes = $service->getAllThemes();
$service->setActiveTheme('theme-slug');
$service->installThemeFromGitHub('username/repository');
$service->cloneTheme('source-slug', 'new-slug', 'New Theme Name');

// Analytics
$stats = $service->getThemeStats();
```

### Theme Model

```php
use Alizharb\FilamentThemesManager\Models\Theme;

// Queries
$themes = Theme::all();
$activeThemes = Theme::active()->get();
$validThemes = Theme::valid()->get();
$theme = Theme::bySlug('theme-slug')->first();

// Statistics
$totalCount = Theme::count();
$activeCount = Theme::getActiveCount();
$validCount = Theme::getValidCount();
```

---

## 🔍 Troubleshooting

### Common Issues

#### Themes Not Appearing

- **Check Permissions**: Verify themes directory is readable
- **Validate JSON**: Ensure `theme.json` files contain valid JSON
- **Clear Cache**: Run `php artisan cache:clear`

#### Preview System Issues

- **Middleware Registration**: Confirm middleware is properly registered
- **Route Accessibility**: Verify preview routes are accessible
- **Session Configuration**: Check session driver and configuration

#### Asset Loading Problems

- **Vite Configuration**: Verify theme's Vite setup is correct
- **Asset Paths**: Ensure paths in `theme.json` are accurate
- **Build Process**: Run `npm run build` for production assets

### Debug Configuration

```php
'debug' => env('THEME_DEBUG', false),
```

### Performance Optimization

- Enable theme caching in production environments
- Configure appropriate cache durations
- Utilize asset compilation for faster loading

---

## 🧪 Development

### Contributing Guidelines

1. **Fork** the repository
2. **Create** feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** changes (`git commit -m 'Add amazing feature'`)
4. **Push** to branch (`git push origin feature/amazing-feature`)
5. **Open** Pull Request

### Testing Suite

```bash
composer test              # Run test suite
vendor/bin/pest            # Run Pest tests directly
vendor/bin/pest --coverage # Generate coverage report
vendor/bin/phpstan analyse # Static analysis
```

### Code Quality

```bash
vendor/bin/pint           # Fix code style
vendor/bin/rector process # Code modernization
```

---

## 📜 License

This package is open-sourced software licensed under the **MIT License**. See [LICENSE.md](LICENSE.md) for details.

---

## 🏆 Credits

### Core Contributors

- **[Ali Harb](https://github.com/alizharb)** - Lead Developer & Maintainer

### Dependencies

- **[Filament](https://filamentphp.com)** - Admin panel framework
- **[qirolab/laravel-themer](https://github.com/qirolab/laravel-themer)** - Core theming engine
- **[Laravel](https://laravel.com)** - PHP framework foundation

---

## 💝 Support the Project

Your support helps maintain and improve this package:

- ⭐ **Star** the repository
- 🐛 **Report** bugs and issues
- 💡 **Suggest** new features
- 📚 **Improve** documentation
- 💰 **[Sponsor development](https://github.com/sponsors/alizharb)**

---

<div align="center">

**Built with ❤️ by [Ali Harb](https://github.com/alizharb)**

_Making Laravel theming professional and accessible_

</div>
