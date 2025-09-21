# Contributing to Filament Themes Manager

Thank you for your interest in contributing to Filament Themes Manager! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have the following installed:

- **PHP 8.2+** with required extensions
- **Composer 2.x**
- **Node.js 18+** and npm
- **Git**

### Development Setup

1. **Fork and clone the repository:**
```bash
git clone https://github.com/your-username/filament-themes-manager.git
cd filament-themes-manager
```

2. **Install dependencies:**
```bash
composer install
npm install
```

3. **Set up environment:**
```bash
cp .env.example .env
```

4. **Run tests to ensure everything works:**
```bash
composer test
```

## 🔄 Development Workflow

### Branching Strategy

- **main**: Production-ready code
- **develop**: Integration branch for features
- **feature/***: New features
- **bugfix/***: Bug fixes
- **hotfix/***: Critical production fixes

### Making Changes

1. **Create a feature branch:**
```bash
git checkout -b feature/your-feature-name
```

2. **Make your changes following our coding standards**

3. **Run quality checks:**
```bash
# Fix code style
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Run tests
vendor/bin/pest

# Check test coverage
vendor/bin/pest --coverage --min=80
```

4. **Commit your changes:**
```bash
git add .
git commit -m "feat: add awesome new feature"
```

5. **Push and create a pull request:**
```bash
git push origin feature/your-feature-name
```

## 📝 Coding Standards

### PHP Code Style

We use **Laravel Pint** for code formatting. Run before committing:

```bash
vendor/bin/pint
```

### Code Quality

- Follow **PSR-12** coding standards
- Use **strict types** in all PHP files
- Add **comprehensive PHPDoc** comments
- Maintain **90%+ test coverage**
- Pass **PHPStan level 9** analysis

### Naming Conventions

- **Classes**: PascalCase (`ThemeManagerService`)
- **Methods**: camelCase (`getAllThemes()`)
- **Variables**: camelCase (`$activeTheme`)
- **Constants**: UPPER_SNAKE_CASE (`DEFAULT_CACHE_DURATION`)
- **Database**: snake_case (`theme_slug`, `is_valid`)

## 🧪 Testing Guidelines

### Test Structure

```
tests/
├── Unit/           # Unit tests for individual classes
├── Feature/        # Integration tests
├── Fixtures/       # Test data and mock objects
└── Pest.php        # Pest configuration
```

### Writing Tests

1. **Use descriptive test names:**
```php
it('can activate theme successfully')
it('throws exception when theme not found')
it('validates theme requirements correctly')
```

2. **Follow AAA pattern:**
```php
it('can clone existing theme', function () {
    // Arrange
    $sourceTheme = createTestTheme('source');

    // Act
    $result = $service->cloneTheme('source', 'target', 'Target Theme');

    // Assert
    expect($result)->toBeTrue();
});
```

3. **Test both happy and error paths**

4. **Use data providers for multiple scenarios:**
```php
it('validates theme requirements', function ($requirement, $expected) {
    // Test logic
})->with([
    ['php' => '8.2', true],
    ['php' => '9.0', false],
]);
```

## 📚 Documentation

### Code Documentation

- **All public methods** must have PHPDoc comments
- **Include parameter and return types**
- **Document thrown exceptions**
- **Provide usage examples** for complex methods

```php
/**
 * Install a theme from a ZIP file.
 *
 * @param string $zipPath Absolute path to the ZIP file
 * @return bool True if installation succeeded, false otherwise
 * @throws InvalidArgumentException If ZIP file doesn't exist
 * @throws RuntimeException If extraction fails
 */
public function installThemeFromZip(string $zipPath): bool
{
    // Implementation
}
```

### README Updates

- Update installation instructions if needed
- Add new features to the features list
- Include examples for new functionality
- Update configuration options

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

1. **Laravel version**
2. **PHP version**
3. **Package version**
4. **Steps to reproduce**
5. **Expected vs actual behavior**
6. **Error messages/stack traces**
7. **Relevant configuration**

### Feature Requests

For feature requests, please provide:

1. **Clear description** of the feature
2. **Use case** and motivation
3. **Proposed implementation** (if applicable)
4. **Potential breaking changes**

## 🚦 Pull Request Process

### Before Submitting

- [ ] **Tests pass** locally
- [ ] **Code style** is compliant
- [ ] **PHPStan** analysis passes
- [ ] **Documentation** is updated
- [ ] **CHANGELOG** is updated (if applicable)

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Tests pass
```

### Review Process

1. **Automated checks** must pass
2. **Code review** by maintainer(s)
3. **Testing** on different environments
4. **Approval** and merge

## 🎯 Areas for Contribution

### High Priority

- **Performance optimizations**
- **Security enhancements**
- **Test coverage improvements**
- **Documentation updates**

### Features We'd Love

- **Theme marketplace integration**
- **Advanced theme validation**
- **Theme update notifications**
- **Multi-tenancy support**
- **Theme performance analytics**

### Good First Issues

Look for issues labeled:
- `good first issue`
- `help wanted`
- `documentation`
- `tests`

## 🌟 Recognition

Contributors will be:

- **Listed in README.md**
- **Mentioned in release notes**
- **Invited to contributor Discord**
- **Eligible for swag** (significant contributions)

## 📞 Getting Help

- **GitHub Discussions**: For questions and ideas
- **GitHub Issues**: For bugs and feature requests
- **Discord**: For real-time chat (link in README)
- **Email**: harbzali@gmail.com for security issues

## 📄 Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of:

- Experience level
- Gender identity and expression
- Sexual orientation
- Disability
- Personal appearance
- Body size
- Race
- Ethnicity
- Age
- Religion
- Nationality

### Expected Behavior

- **Be respectful** and inclusive
- **Be constructive** in feedback
- **Be patient** with newcomers
- **Be collaborative**
- **Focus on** what's best for the community

### Unacceptable Behavior

- Harassment or discrimination
- Trolling or insulting comments
- Public or private harassment
- Publishing others' private information
- Other unprofessional conduct

### Enforcement

Instances of unacceptable behavior may be reported to harbzali@gmail.com. All complaints will be reviewed and investigated promptly and fairly.

---

## 🙏 Thank You

Your contributions make this project better for everyone. Whether you're fixing a typo, adding a feature, or improving documentation, every contribution is valuable and appreciated!

**Happy coding! 🚀**