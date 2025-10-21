# Contributing to WhatsApp Form Builder

Thank you for considering contributing to WhatsApp Form Builder! We welcome contributions from the community.

## How to Contribute

### Reporting Bugs

If you find a bug, please report it by opening an issue on GitHub. Include:
- A clear and descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Your WordPress version, PHP version, and plugin version
- Any error messages from the browser console or PHP logs

### Suggesting Features

We love new ideas! To suggest a feature:
1. Check if it's already been suggested in Issues or Discussions
2. Open a new Discussion to discuss the feature
3. Provide as much detail as possible about the use case
4. Be open to feedback and discussion

### Submitting Pull Requests

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes**
4. **Test your changes thoroughly**
5. **Commit your changes**: `git commit -m "Add your feature"`
6. **Push to your fork**: `git push origin feature/your-feature-name`
7. **Open a Pull Request**

### Code Style

- Follow WordPress Coding Standards
- Use meaningful variable and function names
- Comment your code where necessary
- Keep functions small and focused
- Use proper indentation (tabs for indentation, spaces for alignment)

### PHP Guidelines

```php
// Use proper WordPress hooks
add_action('init', 'my_function');

// Escape output
echo esc_html($variable);
echo esc_attr($attribute);
echo esc_url($url);

// Sanitize input
$value = sanitize_text_field($_POST['value']);

// Use nonces for security
wp_nonce_field('action_name', 'nonce_name');
check_admin_referer('action_name', 'nonce_name');

// Use prepared statements for database queries
$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id);
```

### JavaScript Guidelines

```javascript
// Use jQuery with noConflict wrapper
(function($) {
    'use strict';
    // Your code here
})(jQuery);

// Use consistent naming
var myVariable = 'value';
function myFunction() {}

// Handle errors
$.ajax({
    // ...
}).fail(function(jqXHR, textStatus, errorThrown) {
    console.error('AJAX Error:', errorThrown);
});
```

### CSS Guidelines

```css
/* Use BEM-like naming */
.wafb-form {}
.wafb-form__field {}
.wafb-form__field--required {}

/* Mobile-first approach */
.wafb-form {
    width: 100%;
}

@media (min-width: 768px) {
    .wafb-form {
        width: 600px;
    }
}
```

### Testing

Before submitting a PR:
1. Test on a fresh WordPress installation
2. Test with different themes
3. Test with common plugins (especially caching plugins)
4. Test on different browsers (Chrome, Firefox, Safari, Edge)
5. Test on mobile devices
6. Check for JavaScript errors in console
7. Check for PHP errors in logs

### Documentation

- Update README.md if you change functionality
- Update EXAMPLES.md if you add new features
- Add inline comments for complex code
- Update CHANGELOG.md with your changes

### Commit Messages

Write clear commit messages:
- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Start with a capital letter
- Don't end with a period
- Reference issues and PRs when relevant

Examples:
```
Add phone field validation
Fix reCAPTCHA not loading on cached pages
Update documentation for shortcode parameters
Refactor form submission handler
```

### Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/hindiadobe-png/whatsapp-form-builder.git
```

2. Set up a local WordPress environment (Local by Flywheel, XAMPP, etc.)

3. Symlink or copy the plugin to wp-content/plugins/

4. Activate the plugin in WordPress

5. Start developing!

## Project Structure

```
whatsapp-form-builder/
├── admin/                  # Admin-specific files
│   ├── class-*.php        # Admin classes
│   └── views/             # Admin view templates
├── assets/                # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── images/
├── includes/              # Core plugin classes
├── languages/             # Translation files
├── public/                # Public-facing functionality
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE.txt
└── whatsapp-form-builder.php  # Main plugin file
```

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

## Questions?

If you have questions about contributing, feel free to:
- Open a Discussion on GitHub
- Comment on relevant issues
- Reach out to the maintainers

Thank you for contributing to WhatsApp Form Builder! 🎉
