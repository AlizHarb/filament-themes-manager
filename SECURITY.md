# Security Policy

## Supported Versions

We actively support the following versions of Filament Themes Manager with security updates:

| Version | Supported          | End of Life |
| ------- | ------------------ | ----------- |
| 1.x     | :white_check_mark: | TBD         |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability in Filament Themes Manager, please follow these guidelines:

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via:

1. **Email**: Send a detailed report to **harbzali@gmail.com**
2. **Subject**: `[SECURITY] Filament Themes Manager - Brief Description`
3. **GPG**: You may encrypt your message using our [public key](https://keybase.io/alizharb) (optional)

### What to Include

Please include the following information in your report:

- **Description** of the vulnerability
- **Steps to reproduce** the issue
- **Potential impact** and severity assessment
- **Suggested fix** (if available)
- **Your contact information** for follow-up questions

### Example Report Format

```
Subject: [SECURITY] Filament Themes Manager - Theme Upload Vulnerability

Description:
The theme upload functionality allows execution of arbitrary PHP code through...

Steps to Reproduce:
1. Navigate to theme upload page
2. Create a malicious theme ZIP with...
3. Upload the theme
4. Activate the theme
5. Malicious code executes

Potential Impact:
- Remote code execution
- Server compromise
- Data theft

Affected Versions:
- v1.0.0 and below

Suggested Fix:
Add file content scanning before extraction...
```

## Security Response Process

### Timeline

- **24 hours**: Initial acknowledgment of your report
- **72 hours**: Initial assessment and severity classification
- **7 days**: Detailed analysis and proposed fix
- **14 days**: Patch development and testing
- **21 days**: Security release and public disclosure

### Severity Levels

We classify vulnerabilities using the following severity levels:

#### Critical (CVSS 9.0-10.0)
- Remote code execution
- Authentication bypass
- Privilege escalation to admin
- **Response time**: 24-48 hours

#### High (CVSS 7.0-8.9)
- SQL injection
- Cross-site scripting (XSS)
- Local file inclusion
- **Response time**: 2-7 days

#### Medium (CVSS 4.0-6.9)
- Information disclosure
- CSRF vulnerabilities
- Directory traversal
- **Response time**: 7-14 days

#### Low (CVSS 0.1-3.9)
- Minor information leaks
- Denial of service (low impact)
- **Response time**: 14-30 days

## Security Features

### Current Security Measures

- **Theme Validation**: Comprehensive structure and content validation
- **File Type Restrictions**: Whitelist of allowed file extensions
- **Protected Themes**: Prevention of critical theme deletion
- **Malicious Code Scanning**: Optional security scanning for uploaded themes
- **Path Traversal Protection**: Secure file extraction and handling
- **Input Sanitization**: All user inputs are properly sanitized
- **Permission Checks**: Role-based access control for theme operations

### Security Best Practices

When using Filament Themes Manager:

1. **Keep Updated**: Always use the latest version
2. **Restrict Access**: Limit theme management to trusted administrators
3. **Enable Scanning**: Turn on malicious code detection
4. **Regular Backups**: Backup themes before installation
5. **Monitor Activity**: Review theme-related activities regularly
6. **Secure Environment**: Use HTTPS and secure hosting

### Configuration Security

```php
// config/filament-themes-manager.php

return [
    'security' => [
        // Enable comprehensive validation
        'validate_theme_structure' => true,

        // Enable malicious code scanning
        'scan_malicious_code' => true,

        // Restrict allowed file types
        'allowed_file_types' => [
            'php', 'blade.php', 'css', 'scss', 'js', 'vue', 'json',
            'md', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'
        ],

        // Protect critical themes
        'protected_themes' => ['default'],
    ],
];
```

## Responsible Disclosure

We follow responsible disclosure practices:

1. **Private Disclosure**: Initial report kept private
2. **Coordinated Response**: Work together on fix development
3. **Testing Period**: Allow time for patch testing
4. **Public Disclosure**: Announce after fix is available
5. **Credit**: Security researchers credited in release notes

## Security Credits

We would like to thank the following security researchers who have responsibly disclosed vulnerabilities:

- *None yet - be the first!*

## Security Resources

### Learning Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Guide](https://phpsec.org/)

### Security Tools

- [PHP Security Checker](https://security.symfony.com/)
- [Composer Security Audit](https://getcomposer.org/doc/03-cli.md#audit)
- [SensioLabs Security Checker](https://github.com/sensiolabs/security-checker)

## Contact Information

- **Security Email**: harbzali@gmail.com
- **General Contact**: Same email for non-security issues
- **GitHub**: [@alizharb](https://github.com/alizharb)

## Security Updates

Subscribe to security updates:

- **GitHub Watch**: Enable notifications for this repository
- **Security Advisories**: Follow GitHub Security Advisories
- **Newsletter**: Coming soon

---

**Thank you for helping keep Filament Themes Manager secure!**