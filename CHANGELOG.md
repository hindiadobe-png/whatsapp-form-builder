# Changelog

All notable changes to the WhatsApp Form Builder plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-21

### Added
- Initial release of WhatsApp Form Builder plugin
- Drag-and-drop form builder interface
- Basic field types:
  - Text Field
  - Email Field
  - Phone Field
  - Message Field (textarea)
- Form management features:
  - Create new forms
  - Save forms to database
  - Load existing forms
  - Delete forms with confirmation
  - Edit field settings (label, placeholder, required)
- Live form preview
- Field reordering via drag-and-drop
- Admin menu integration with WordPress dashboard
- Responsive design for mobile and tablet devices
- Database table creation on plugin activation
- WordPress security implementations:
  - Capability checks (manage_options required)
  - Nonce verification for all AJAX requests
  - Input sanitization (sanitize_text_field, wp_kses_post, absint)
  - Output escaping (esc_html, esc_attr, esc_url)
  - CSRF protection
  - Prepared database statements
  - Direct file access prevention
- Comprehensive documentation:
  - README.md with installation and usage instructions
  - SECURITY.md with security implementation details
  - TESTING.md with testing procedures
  - DEVELOPER.md with architecture and extension guide
  - CHANGELOG.md for version tracking
- Translation-ready (all strings wrapped in translation functions)
- WordPress coding standards compliance

### Security
- All admin functionality requires `manage_options` capability
- All AJAX requests protected with nonces
- All user inputs sanitized
- All outputs escaped
- All database queries use prepared statements
- Direct file access prevented on all PHP files

### Technical Details
- Minimum WordPress version: 5.0
- Minimum PHP version: 7.0
- Uses jQuery UI for drag-and-drop functionality
- Modular architecture for easy extension
- Database table: `wp_wafb_forms`
- Text domain: `whatsapp-form-builder`

## [Unreleased]

### Planned Features
- WhatsApp integration for form submissions
- Additional field types (select, checkbox, radio)
- Form shortcode for frontend display
- Email notifications
- Form submission storage
- Export/import forms
- Form analytics
- Custom styling options
- Conditional logic
- File upload fields
- Multi-page forms
- Form templates
- Duplicate form functionality
- Bulk delete operations

---

## Version History

### Version Numbering

- **Major version (X.0.0)**: Incompatible API changes or major features
- **Minor version (0.X.0)**: New functionality in a backward-compatible manner
- **Patch version (0.0.X)**: Backward-compatible bug fixes

### Release Notes

#### 1.0.0 (2025-10-21)
First stable release with core form builder functionality and comprehensive security implementations.

---

[1.0.0]: https://github.com/hindiadobe-png/whatsapp-form-builder/releases/tag/v1.0.0
[Unreleased]: https://github.com/hindiadobe-png/whatsapp-form-builder/compare/v1.0.0...HEAD
