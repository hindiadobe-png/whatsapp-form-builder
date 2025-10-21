# Developer Guide

This guide provides technical information for developers who want to understand, extend, or contribute to the WhatsApp Form Builder plugin.

## Architecture Overview

The plugin follows WordPress plugin best practices with a modular architecture:

```
whatsapp-form-builder/
├── whatsapp-form-builder.php    # Main plugin file (bootstrap)
├── includes/                     # Core functionality
├── admin/                        # Admin interface
└── assets/                       # Frontend resources
```

## Core Components

### 1. Main Plugin File (`whatsapp-form-builder.php`)

The entry point that:
- Defines plugin constants
- Registers activation/deactivation hooks
- Initializes the core plugin class

**Key Constants:**
```php
WAFB_VERSION        // Plugin version
WAFB_PLUGIN_DIR     // Absolute path to plugin directory
WAFB_PLUGIN_URL     // URL to plugin directory
WAFB_PLUGIN_BASENAME // Plugin basename
```

### 2. Activator (`includes/class-wafb-activator.php`)

Handles plugin activation:
- Creates database table
- Sets default options
- Uses `dbDelta()` for safe table creation

**Database Table:**
```sql
CREATE TABLE wp_wafb_forms (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    form_name varchar(255) NOT NULL,
    form_data longtext NOT NULL,
    form_settings longtext,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)
```

### 3. Deactivator (`includes/class-wafb-deactivator.php`)

Handles plugin deactivation:
- Cleans up temporary data
- Does NOT remove database table (data persistence)

### 4. Core Class (`includes/class-wafb-core.php`)

Orchestrates the plugin:
- Loads dependencies
- Registers hooks
- Initializes admin functionality

**Hook Registration:**
```php
add_action( 'admin_menu', array( $plugin_admin, 'add_admin_menu' ) );
add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
add_action( 'wp_ajax_wafb_save_form', array( $plugin_admin, 'ajax_save_form' ) );
add_action( 'wp_ajax_wafb_load_form', array( $plugin_admin, 'ajax_load_form' ) );
add_action( 'wp_ajax_wafb_delete_form', array( $plugin_admin, 'ajax_delete_form' ) );
```

### 5. Admin Class (`admin/class-wafb-admin.php`)

Handles all admin functionality:
- Menu registration
- Asset enqueueing
- AJAX handlers
- Security checks

**Key Methods:**

#### `add_admin_menu()`
Registers the admin menu page:
```php
add_menu_page(
    __( 'WhatsApp Form Builder', 'whatsapp-form-builder' ),  // Page title
    __( 'Form Builder', 'whatsapp-form-builder' ),           // Menu title
    'manage_options',                                         // Capability
    'wafb-form-builder',                                     // Menu slug
    array( $this, 'display_admin_page' ),                   // Callback
    'dashicons-feedback',                                    // Icon
    30                                                       // Position
);
```

#### `enqueue_scripts()`
Conditionally loads assets:
```php
if ( 'toplevel_page_wafb-form-builder' !== $hook ) {
    return; // Only load on plugin page
}
```

#### AJAX Handlers
All follow this pattern:
1. Verify nonce
2. Check capabilities
3. Sanitize inputs
4. Process request
5. Return JSON response

### 6. Admin Display (`admin/partials/wafb-admin-display.php`)

Template file for the admin interface:
- Form management sidebar
- Drag-and-drop builder
- Field palette
- Form preview
- Field settings modal

## JavaScript Architecture

### Main File (`assets/js/wafb-admin.js`)

Implements drag-and-drop functionality using jQuery UI.

**Key Functions:**

#### `initDragAndDrop()`
Sets up jQuery UI sortable/draggable:
```javascript
$('.wafb-field-item').draggable({
    helper: 'clone',
    connectToSortable: '#wafb-form-fields',
    // ...
});

$('#wafb-form-fields').sortable({
    placeholder: 'wafb-field-placeholder-sort',
    receive: function(event, ui) {
        // Handle new field drop
    },
    // ...
});
```

#### `createFormField(fieldType)`
Dynamically creates field HTML:
```javascript
const fieldHtml = `
    <div class="wafb-form-field" data-field-id="${fieldId}" data-field-type="${fieldType}">
        <!-- Field structure -->
    </div>
`;
```

#### `serializeFormFields()`
Converts DOM to JSON:
```javascript
$('#wafb-form-fields .wafb-form-field').each(function() {
    fields.push({
        type: $field.data('field-type'),
        label: $field.find('.field-label').val(),
        // ...
    });
});
```

#### `updatePreview()`
Syncs builder with preview:
- Runs after every field change
- Creates read-only representation
- Shows how form will look

## CSS Architecture

### Main File (`assets/css/wafb-admin.css`)

Organized into sections:
1. Container layout (flexbox)
2. Sidebar styles
3. Builder styles
4. Field palette
5. Drop zone
6. Form fields
7. Preview
8. Modal
9. Notifications
10. Responsive breakpoints

**Key Classes:**

- `.wafb-admin-wrapper` - Main container
- `.wafb-container` - Flex layout
- `.wafb-sidebar` - Left sidebar
- `.wafb-main-content` - Right content area
- `.wafb-builder-wrapper` - Builder container
- `.wafb-fields-palette` - Available fields
- `.wafb-drop-zone` - Drop area
- `.wafb-form-field` - Individual field
- `.wafb-form-preview` - Preview area
- `.wafb-modal` - Settings modal

## Security Implementation

### 1. Input Sanitization

Always sanitize based on data type:

```php
// Text
$text = sanitize_text_field( wp_unslash( $_POST['text'] ) );

// HTML
$html = wp_kses_post( wp_unslash( $_POST['html'] ) );

// Integer
$id = absint( $_POST['id'] );

// URL
$url = esc_url_raw( $_POST['url'] );

// Email
$email = sanitize_email( $_POST['email'] );
```

### 2. Output Escaping

Always escape based on context:

```php
// Text in HTML
echo esc_html( $text );

// Attributes
echo '<div data-id="' . esc_attr( $id ) . '">';

// URL
echo '<a href="' . esc_url( $url ) . '">';

// Translatable text
esc_html_e( 'Text', 'domain' );
esc_attr_e( 'Text', 'domain' );
```

### 3. Nonce Verification

Always verify nonces:

```php
// Check
check_ajax_referer( 'wafb_admin_nonce', 'nonce' );

// Alternative
if ( ! wp_verify_nonce( $_POST['nonce'], 'wafb_admin_nonce' ) ) {
    wp_die( 'Invalid nonce' );
}
```

### 4. Capability Checks

Always check capabilities:

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => 'Permission denied' ) );
}
```

### 5. Database Queries

Always use prepared statements:

```php
// Good
$wpdb->get_row( $wpdb->prepare( 
    "SELECT * FROM $table WHERE id = %d", 
    $id 
) );

// Bad - NEVER DO THIS
$wpdb->get_row( "SELECT * FROM $table WHERE id = $id" );
```

## Extending the Plugin

### Adding New Field Types

1. **Add to palette** in `admin/partials/wafb-admin-display.php`:
```php
<div class="wafb-field-item" data-field-type="select">
    <span class="dashicons dashicons-menu"></span>
    <span class="field-label"><?php esc_html_e( 'Select Field', 'whatsapp-form-builder' ); ?></span>
</div>
```

2. **Handle in JavaScript** in `assets/js/wafb-admin.js`:
```javascript
switch(fieldType) {
    case 'select':
        fieldLabel = 'Select Field';
        fieldPlaceholder = 'Choose an option';
        inputType = 'select';
        icon = 'dashicons-menu';
        break;
}
```

3. **Update preview rendering** in `updatePreview()`:
```javascript
if (fieldType === 'select') {
    inputHtml = `<select>${/* options */}</select>`;
}
```

### Adding Form Settings

1. **Update database schema** in `class-wafb-activator.php`
2. **Add UI fields** in admin display
3. **Update save handler** in `ajax_save_form()`
4. **Update load handler** in `ajax_load_form()`

### Adding Export/Import

Example implementation:

```php
// Export
public function ajax_export_form() {
    check_ajax_referer( 'wafb_admin_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }
    
    $form_id = absint( $_POST['form_id'] );
    $form = $wpdb->get_row( $wpdb->prepare( "..." ) );
    
    wp_send_json_success( array( 'export' => $form ) );
}

// Import
public function ajax_import_form() {
    check_ajax_referer( 'wafb_admin_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }
    
    $import_data = json_decode( wp_unslash( $_POST['import_data'] ) );
    // Validate and import
}
```

## Coding Standards

### PHP

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use tabs for indentation
- Space after control structures
- Yoda conditions for comparisons
- Single quotes for strings (unless interpolating)
- Proper file headers and DocBlocks

### JavaScript

Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/):

- Use tabs for indentation
- Semicolons required
- Single quotes for strings
- Proper variable naming (camelCase)

### CSS

Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/):

- Use tabs for indentation
- Properties in alphabetical order
- Proper selector naming (kebab-case)
- Include vendor prefixes when needed

## Debugging

### Enable WordPress Debug Mode

In `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

### Check Error Log

```bash
tail -f wp-content/debug.log
```

### JavaScript Console

```javascript
console.log('Debug:', data);
console.error('Error:', error);
```

### Database Queries

```php
global $wpdb;
echo $wpdb->last_query;
echo $wpdb->last_error;
```

## Testing

### Manual Testing

See `TESTING.md` for comprehensive testing guide.

### Unit Testing (Future)

Set up PHPUnit:
```bash
composer require --dev phpunit/phpunit
```

Create tests:
```php
class Test_WAFB_Admin extends WP_UnitTestCase {
    public function test_capability_check() {
        // Test code
    }
}
```

### JavaScript Testing (Future)

Set up Jest:
```bash
npm install --save-dev jest
```

Create tests:
```javascript
describe('Form Builder', () => {
    test('creates field', () => {
        // Test code
    });
});
```

## Best Practices

1. **Always sanitize input** - Never trust user data
2. **Always escape output** - Prevent XSS attacks
3. **Use prepared statements** - Prevent SQL injection
4. **Check capabilities** - Verify user permissions
5. **Verify nonces** - Prevent CSRF attacks
6. **Use WordPress APIs** - Don't reinvent the wheel
7. **Make it translatable** - Wrap strings in translation functions
8. **Document your code** - Help future developers
9. **Test thoroughly** - Before releasing changes
10. **Follow standards** - Maintain code consistency

## Performance Optimization

### Database

- Add indexes for frequently queried columns
- Use transients for caching
- Limit query results

### JavaScript

- Minify in production
- Combine files when possible
- Use event delegation

### CSS

- Minify in production
- Remove unused styles
- Use efficient selectors

## Troubleshooting

### Plugin Won't Activate

- Check PHP version (7.0+)
- Check file permissions
- Check error logs

### Forms Not Saving

- Check database connection
- Verify AJAX endpoint
- Check nonce validity
- Check user capabilities

### Drag and Drop Not Working

- Ensure jQuery UI loaded
- Check for JavaScript errors
- Verify jQuery version

### Styles Not Loading

- Check file paths
- Verify hook name
- Check conditional loading

## Contributing

1. Fork the repository
2. Create a feature branch
3. Follow coding standards
4. Test thoroughly
5. Submit pull request

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [jQuery UI Documentation](https://jqueryui.com/)

## Support

For questions or issues:
- GitHub Issues: [Create an issue](https://github.com/hindiadobe-png/whatsapp-form-builder/issues)
- Documentation: See README.md, SECURITY.md, TESTING.md

## License

GPL v2 or later - Same as WordPress
