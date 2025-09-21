# Pull Request

## Description

<!-- Provide a brief description of the changes in this PR -->

## Type of Change

- [ ] 🐛 Bug fix (non-breaking change that fixes an issue)
- [ ] ✨ New feature (non-breaking change that adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📚 Documentation update
- [ ] 🔧 Maintenance (dependency updates, code cleanup, etc.)
- [ ] ⚡ Performance improvement
- [ ] 🔒 Security enhancement

## Related Issues

<!-- Link any related issues here -->
Fixes #(issue number)
Closes #(issue number)
Relates to #(issue number)

## Changes Made

<!-- Provide a more detailed description of the changes -->

-
-
-

## Testing

<!-- Describe how you tested these changes -->

### Test Coverage
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing completed
- [ ] All existing tests pass

### Test Commands
```bash
# Run specific tests for this change
vendor/bin/pest tests/Unit/YourNewTest.php

# Run all tests
vendor/bin/pest

# Check coverage
vendor/bin/pest --coverage
```

## Code Quality

- [ ] Code follows the project's style guidelines
- [ ] Self-review of code completed
- [ ] Code is properly documented (PHPDoc comments)
- [ ] No debugging code left in
- [ ] Error handling is appropriate

### Quality Checks
```bash
# Fix code style
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Check for code issues
vendor/bin/rector process --dry-run
```

## Documentation

- [ ] Documentation updated (if applicable)
- [ ] README.md updated (if applicable)
- [ ] CHANGELOG.md updated (if applicable)
- [ ] API documentation updated (if applicable)

## Breaking Changes

<!-- If this is a breaking change, describe what breaks and how to migrate -->

### Migration Guide
<!-- Provide migration instructions for breaking changes -->

## Security

- [ ] Security implications have been considered
- [ ] No sensitive data is exposed
- [ ] Input validation is proper
- [ ] Authorization checks are in place (if applicable)

## Performance

- [ ] Performance implications have been considered
- [ ] No N+1 queries introduced
- [ ] Caching strategy is appropriate (if applicable)
- [ ] Memory usage is reasonable

## Deployment

- [ ] No special deployment steps required
- [ ] Database migrations included (if applicable)
- [ ] Configuration changes documented (if applicable)
- [ ] Environment variables updated (if applicable)

## Screenshots/Videos

<!-- Add screenshots or videos if the changes affect the UI -->

## Additional Notes

<!-- Any additional information that reviewers should know -->

## Checklist

- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my code
- [ ] I have commented my code where necessary
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] Any dependent changes have been merged and published

## Reviewer Notes

<!-- Anything specific you want reviewers to focus on -->

---

<!--
Thank you for your contribution! 🎉

Please ensure all checkboxes are ticked before requesting a review.
If you need help with any of these requirements, feel free to ask in the comments.
-->