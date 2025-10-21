# WhatsApp Form Builder - Examples & Documentation

## Quick Start Example

### 1. Create a Simple Contact Form

```php
// After installing and activating the plugin:
// 1. Go to WhatsApp Forms > Add New
// 2. Name your form "Contact Us"
// 3. Add these fields:
//    - Name (Text Field, Required)
//    - Email (Email Field, Required)
//    - Phone (Phone Field, Required)
//    - Message (Text Area, Required)
// 4. Configure settings:
//    - WhatsApp Number: +1234567890
//    - Form Title: "Get in Touch"
//    - Submit Button: "Send Message"
// 5. Save the form
// 6. Copy the shortcode [whatsapp_form id="1"]
// 7. Paste it in any page or post
```

## Advanced Examples

### Example 1: Service Request Form

Fields:
- **Name** (Text, Required)
- **Email** (Email, Required)
- **Phone** (Phone, Required)
- **Service Type** (Dropdown, Required)
  - Options: Web Design, SEO, Marketing, Consulting
- **Budget** (Dropdown)
  - Options: $1k-$5k, $5k-$10k, $10k+
- **Project Details** (Textarea, Required)

Settings:
- Enable reCAPTCHA
- Custom message template: "New service request from {name}. Service: {service_type}. Budget: {budget}. Details: {project_details}. Contact: {email}, {phone}. From: {page_url}"

### Example 2: Multi-Language Support Form

For Arabic/Hebrew sites:
1. Enable RTL in form settings
2. Add Arabic labels to fields
3. The form will automatically display right-to-left

### Example 3: Lead Generation with Auto-Response (Pro)

Settings:
- Enable Webhook: https://yoursite.com/api/leads
- Enable Auto-Response
- Auto-Response Subject: "Thank you for contacting us!"
- Auto-Response Message: "We received your message and will get back to you soon."

## Developer Examples

### Custom Field Validation

```php
add_filter('wafb_validate_field', function($is_valid, $field, $value) {
    if ($field['name'] === 'custom_field') {
        // Add custom validation logic
        if (strlen($value) < 10) {
            $is_valid = false;
        }
    }
    return $is_valid;
}, 10, 3);
```

### Modify WhatsApp Message Format

```php
add_filter('wafb_whatsapp_message', function($message, $form_data, $form_id) {
    // Add custom formatting
    $message = "🎯 NEW LEAD\n\n" . $message;
    return $message;
}, 10, 3);
```

### Custom Form Styling

```php
add_action('wp_head', function() {
    ?>
    <style>
        .wafb-form-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .wafb-submit-btn {
            background: #FFD700;
            color: #000;
        }
    </style>
    <?php
});
```

### Save to Custom Database

```php
add_action('wafb_after_form_submit', function($form_id, $form_data, $lead_id) {
    // Save to your custom table or external API
    $api_url = 'https://your-crm.com/api/leads';
    wp_remote_post($api_url, array(
        'body' => json_encode($form_data),
        'headers' => array('Content-Type' => 'application/json')
    ));
}, 10, 3);
```

### Add Custom Field Type

```php
add_filter('wafb_field_types', function($field_types) {
    $field_types['date'] = array(
        'label' => 'Date Picker',
        'icon' => 'dashicons-calendar',
        'description' => 'Select a date'
    );
    return $field_types;
});

add_action('wafb_render_field_date', function($field) {
    ?>
    <input type="date" 
           name="<?php echo esc_attr($field['name']); ?>"
           id="wafb-<?php echo esc_attr($field['name']); ?>"
           class="wafb-field-input"
           <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>>
    <?php
});
```

## Common Use Cases

### 1. E-commerce Product Inquiry

Perfect for product pages where customers can quickly inquire about products via WhatsApp.

Fields: Name, Email, Phone, Product Name, Quantity, Message

### 2. Real Estate Property Inquiry

Ideal for real estate websites to capture property inquiries.

Fields: Name, Email, Phone, Property Type (Dropdown), Location, Budget Range, Message

### 3. Restaurant Reservations

Quick reservation form that sends details to restaurant's WhatsApp.

Fields: Name, Phone, Date (future feature), Time, Number of Guests, Special Requests

### 4. Job Application Pre-Screening

Collect basic information before directing to full application.

Fields: Name, Email, Phone, Position Applied, Experience Level, Resume URL, Cover Letter

### 5. Event Registration

Capture event registrations and send confirmation via WhatsApp.

Fields: Name, Email, Phone, Event (Dropdown), Number of Tickets, Dietary Restrictions

## Integration Examples

### Google Analytics Tracking

```javascript
jQuery(document).on('wafb_form_success', function(e, form_id) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submission', {
            'event_category': 'WhatsApp Form',
            'event_label': 'Form ID: ' + form_id,
            'value': 1
        });
    }
});
```

### Facebook Pixel

```javascript
jQuery(document).on('wafb_form_success', function(e, form_id) {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Lead', {
            content_name: 'WhatsApp Form',
            content_category: 'Contact'
        });
    }
});
```

## Troubleshooting

### Form Not Displaying
- Check if shortcode is correct: `[whatsapp_form id="X"]`
- Verify form status is "Active"
- Clear cache if using caching plugin

### WhatsApp Link Not Working
- Ensure WhatsApp number includes country code
- Remove spaces and special characters except +
- Test the number format: +1234567890

### reCAPTCHA Not Showing
- Verify Site Key and Secret Key are correct
- Check if reCAPTCHA is enabled in both global settings and form settings
- Ensure no JavaScript conflicts

### Leads Not Saving
- Check if "Save Leads" is enabled in Settings > General
- Verify database tables were created (check during activation)
- Check PHP error logs for database errors

## Performance Tips

1. **Optimize Database**: Regularly clean old leads
2. **Cache Forms**: Use transients for form data
3. **Minimize Fields**: Only ask for essential information
4. **Lazy Load**: Load forms only when needed
5. **CDN Assets**: Use CDN for CSS/JS files (Pro feature)

## Security Best Practices

1. Always enable reCAPTCHA for public forms
2. Regularly update the plugin
3. Use strong validation on all fields
4. Limit submission rate (Pro feature)
5. Monitor suspicious activity in leads

## Backup and Migration

### Backup Forms
```php
// Export forms as JSON
$forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wafb_forms");
file_put_contents('forms-backup.json', json_encode($forms));
```

### Import Forms
```php
// Import forms from JSON
$forms = json_decode(file_get_contents('forms-backup.json'), true);
foreach ($forms as $form) {
    unset($form['id']);
    $wpdb->insert($wpdb->prefix . 'wafb_forms', $form);
}
```

## Support

For additional help and support:
- Documentation: [GitHub Wiki](https://github.com/hindiadobe-png/whatsapp-form-builder/wiki)
- Issues: [GitHub Issues](https://github.com/hindiadobe-png/whatsapp-form-builder/issues)
- Discussions: [GitHub Discussions](https://github.com/hindiadobe-png/whatsapp-form-builder/discussions)
