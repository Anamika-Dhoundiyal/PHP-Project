# Contributing Guidelines

Thank you for your interest in contributing to the Grocery Store Management System! This document provides guidelines for contributing to the project.

## 🎯 Before You Start

- Read the [README.md](README.md) to understand the project
- Check existing [Issues](https://github.com/Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM/issues) to avoid duplicates
- Fork the repository
- Clone your fork locally

## 🔄 Development Workflow

### 1. Create a Branch
```bash
git checkout -b feature/your-feature-name
```

Branch naming conventions:
- `feature/` - New features
- `bugfix/` - Bug fixes
- `docs/` - Documentation updates
- `refactor/` - Code refactoring
- `test/` - Adding tests

### 2. Make Your Changes

Follow these guidelines:
- One logical change per commit
- Keep commits focused and atomic
- Write clear commit messages

### 3. Commit Message Format
```
[TYPE] Brief description (50 chars max)

Optional detailed explanation of the changes and why they were made.
Keep lines under 72 characters.

Fixes #123 (if applicable)
```

Examples:
```
[FEATURE] Add order status email notifications
[BUGFIX] Fix cart quantity update issue on checkout
[DOCS] Update database schema documentation
```

### 4. Test Your Changes

Before submitting:
- Test locally with XAMPP/WAMP
- Verify all links work
- Test with different user roles (admin, customer)
- Check for PHP errors in error logs
- Test responsive design on mobile browsers

### 5. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Create PR on GitHub with:
- Clear title
- Description of changes
- Why this change is needed
- Testing done
- Screenshots (if UI changes)

## 📋 Code Standards

### PHP Code Style
```php
<?php
// Follow PSR-12 standards

class ExampleClass
{
    public function exampleMethod($parameter)
    {
        if ($condition) {
            return true;
        }

        return false;
    }
}
?>
```

### Database Queries
Always use prepared statements:
```php
// Good - prepared statement
$sql = "SELECT * FROM products WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);

// Avoid - SQL injection risk
$sql = "SELECT * FROM products WHERE category = '" . $category . "'";
```

### HTML/CSS
- Use semantic HTML5 elements
- Follow Bootstrap conventions
- Comment complex sections
- Keep CSS organized by component

### JavaScript
- Use clear variable names
- Comment complex logic
- Avoid global variables
- Use `const` by default, `let` when needed

## 🐛 Reporting Bugs

Create an issue with:
1. **Title**: Clear, descriptive summary
2. **Description**: What happened vs. expected behavior
3. **Steps to Reproduce**: Exact steps to trigger bug
4. **Environment**: PHP version, MySQL version, Browser
5. **Screenshots**: If visually broken
6. **Error Messages**: Full error message from logs

Template:
```
**Bug Description**
[Describe the bug]

**Steps to Reproduce**
1. [Step 1]
2. [Step 2]

**Expected Behavior**
[What should happen]

**Actual Behavior**
[What actually happened]

**Environment**
- PHP Version: 7.4
- MySQL Version: 5.7
- Browser: Chrome 90
```

## 💡 Feature Requests

Describe the feature with:
1. **What problem does it solve?**
2. **How would it work?**
3. **Mockups or examples** (if applicable)
4. **Why is it important?**

## 📚 Documentation

- Update README.md for new features
- Add code comments for complex logic
- Update DEPLOYMENT.md if deployment changes
- Include examples for new features

## 🔍 Review Process

1. **Automated Checks**: GitHub checks must pass
2. **Code Review**: Owner reviews code and changes
3. **Testing**: Changes verified against requirements
4. **Approval**: PR approved by maintainer
5. **Merge**: Changes merged to main branch

## 🤝 Community Standards

- Be respectful and constructive
- Ask questions if unclear
- Help others with issues
- Share knowledge and experience
- No harassment or discrimination

## ✅ Checklist Before Submitting PR

- [ ] Branch is up-to-date with main
- [ ] Code follows style guidelines
- [ ] Comments added for complex logic
- [ ] No breaking changes or documented if necessary
- [ ] Tested locally
- [ ] No debug code or console logs
- [ ] Commit messages are clear
- [ ] Related issue(s) linked
- [ ] Documentation updated

## 🎓 Resources

- [PHP Best Practices](https://www.php-fig.org/psr/psr-12/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [Git Workflow Guide](https://guides.github.com/introduction/flow/)

## 📞 Questions?

- Check existing issues for similar questions
- Create a discussion for general questions
- Comment on related issues
- Respect maintainer's time

## 🎉 Thank You!

Your contributions help make this project better for everyone. We appreciate your effort and dedication!

---

**Happy Coding!** 🚀
