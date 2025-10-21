# Changelog

All notable changes to WhatsApp Form Builder will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- Initial release of WhatsApp Form Builder plugin
- Drag-and-drop form builder interface
- Multiple field types: text, email, phone, textarea, select, radio, checkbox, number, URL, date
- Form validation with hints and error messages
- Google reCAPTCHA integration
- Shortcode support for embedding forms
- RTL (Right-to-Left) language support
- Lead management with filtering and pagination
- Lead export to CSV (Pro)
- WhatsApp message generation with page URL
- Multiple recipient support (Pro)
- Advanced message templates (Pro)
- WhatsApp Business API integration (Pro)
- Webhook integration (Pro)
- Auto-response emails (Pro)
- License management system
- Admin settings page
- Hooks and filters for extensibility:
  - `wfbp_after_activation`
  - `wfbp_after_deactivation`
  - `wfbp_after_save_lead`
  - `wfbp_before_save_lead`
  - `wfbp_before_send_message`
  - `wfbp_after_send_message`
  - `wfbp_before_form_render`
  - `wfbp_after_form_render`
  - `wfbp_form_validation_errors`
  - `wfbp_whatsapp_message`
  - `wfbp_format_message`
  - `wfbp_webhook_data`

### Security
- Input sanitization using WordPress functions
- Output escaping
- Nonce verification for all AJAX requests
- Capability checks for admin actions
- Prepared SQL statements to prevent SQL injection
- XSS prevention
- CSRF protection

### Database
- Created `wp_wfbp_forms` table for form configurations
- Created `wp_wfbp_leads` table for form submissions
- Created `wp_wfbp_logs` table for activity logs

### UI/UX
- Modern, responsive admin interface
- Mobile-friendly form display
- Clean, customizable form styling
- Loading states and animations
- User-friendly error messages
- Copy shortcode with one click

### Performance
- Optimized database queries
- Minimal JavaScript footprint
- CSS only loaded when needed
- AJAX form submission without page reload

### Compatibility
- WordPress 5.0+
- PHP 7.0+
- MySQL 5.6+
- jQuery (bundled with WordPress)

### Documentation
- Comprehensive README.md
- Detailed INSTALL.md guide
- Inline code documentation
- POT file for translations

## [Unreleased]

### Planned Features
- Conditional logic for fields
- File upload support
- Email notifications
- Analytics dashboard
- Multi-step forms
- Custom CSS editor
- Form templates library
- Duplicate form functionality
- Bulk actions for leads
- Advanced spam protection
- Integration with popular plugins
- REST API endpoints

---

## Version History

### Version 1.0.0 (2024-01-01)
Initial release with core features and Pro version support.

---

For support and bug reports, please visit: https://github.com/hindiadobe-png/whatsapp-form-builder/issues
