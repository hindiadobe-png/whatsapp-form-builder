# Code Examples - WhatsApp Form Builder

This document provides code examples for extending the WhatsApp Form Builder plugin using hooks and filters.

## Table of Contents
- [Form Rendering](#form-rendering)
- [Lead Management](#lead-management)
- [Message Customization](#message-customization)
- [Validation](#validation)
- [Webhooks](#webhooks)

## Form Rendering

### Add Custom HTML Before Form

```php
add_filter('wfbp_before_form_render', 'custom_before_form_content', 10, 2);

function custom_before_form_content($html, $form) {
    $custom_html = '<div class="custom-form-header">';
    $custom_html .= '<h2>Contact Us via WhatsApp</h2>';
    $custom_html .= '<p>Fill out the form below to send us a message on WhatsApp.</p>';
    $custom_html .= '</div>';
    
    return $html . $custom_html;
}
```

### Add Custom HTML After Form

```php
add_filter('wfbp_after_form_render', 'custom_after_form_content', 10, 2);

function custom_after_form_content($html, $form) {
    $custom_html = '<div class="custom-form-footer">';
    $custom_html .= '<p><small>By submitting this form, you agree to our privacy policy.</small></p>';
    $custom_html .= '</div>';
    
    return $html . $custom_html;
}
```

## Lead Management

### Modify Form Data Before Saving

```php
add_filter('wfbp_before_save_lead', 'modify_lead_data', 10, 2);

function modify_lead_data($form_data, $form_id) {
    // Add timestamp
    $form_data['submission_time'] = current_time('mysql');
    
    // Add custom field
    $form_data['source'] = 'website';
    
    // Sanitize phone number
    if (isset($form_data['phone'])) {
        $form_data['phone'] = preg_replace('/[^0-9+]/', '', $form_data['phone']);
    }
    
    return $form_data;
}
```

### Perform Actions After Lead is Saved

```php
add_action('wfbp_after_save_lead', 'notify_admin_new_lead', 10, 3);

function notify_admin_new_lead($lead_id, $form_data, $form_id) {
    // Send email notification to admin
    $admin_email = get_option('admin_email');
    $subject = 'New WhatsApp Form Submission';
    $message = 'A new form has been submitted. Lead ID: ' . $lead_id;
    
    wp_mail($admin_email, $subject, $message);
    
    // Log to external service
    // wp_remote_post('https://your-api.com/log', array(
    //     'body' => array('lead_id' => $lead_id, 'data' => $form_data)
    // ));
}
```

### Send Data to CRM After Lead Save

```php
add_action('wfbp_after_save_lead', 'send_to_crm', 10, 3);

function send_to_crm($lead_id, $form_data, $form_id) {
    // Example: Send to a CRM API
    $crm_api_url = 'https://your-crm.com/api/contacts';
    $crm_api_key = 'your-api-key';
    
    $response = wp_remote_post($crm_api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $crm_api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'name' => isset($form_data['name']) ? $form_data['name'] : '',
            'email' => isset($form_data['email']) ? $form_data['email'] : '',
            'phone' => isset($form_data['phone']) ? $form_data['phone'] : '',
            'source' => 'WhatsApp Form',
        )),
    ));
    
    if (is_wp_error($response)) {
        error_log('CRM sync failed: ' . $response->get_error_message());
    }
}
```

## Message Customization

### Customize WhatsApp Message Format

```php
add_filter('wfbp_format_message', 'custom_message_format', 10, 3);

function custom_message_format($message, $form_data, $form_settings) {
    // Create a custom formatted message
    $custom_message = "🔔 *New Contact Request*\n\n";
    
    if (isset($form_data['name'])) {
        $custom_message .= "👤 *Name:* " . $form_data['name'] . "\n";
    }
    
    if (isset($form_data['email'])) {
        $custom_message .= "📧 *Email:* " . $form_data['email'] . "\n";
    }
    
    if (isset($form_data['phone'])) {
        $custom_message .= "📱 *Phone:* " . $form_data['phone'] . "\n";
    }
    
    if (isset($form_data['message'])) {
        $custom_message .= "\n💬 *Message:*\n" . $form_data['message'] . "\n";
    }
    
    $custom_message .= "\n⏰ *Time:* " . current_time('F j, Y g:i a');
    
    return $custom_message;
}
```

### Modify WhatsApp Message Before Sending

```php
add_filter('wfbp_whatsapp_message', 'add_tracking_to_message', 10, 2);

function add_tracking_to_message($encoded_message, $phone) {
    // Add UTM parameters or tracking info
    $decoded = rawurldecode($encoded_message);
    $decoded .= "\n\n🔗 Track: https://yoursite.com/track?ref=wa";
    
    return rawurlencode($decoded);
}
```

### Actions Before/After Sending Message

```php
add_action('wfbp_before_send_message', 'log_before_send', 10, 3);

function log_before_send($phone_number, $message, $form_id) {
    error_log("Sending WhatsApp message to {$phone_number} from form {$form_id}");
}

add_action('wfbp_after_send_message', 'log_after_send', 10, 4);

function log_after_send($phone_number, $message, $form_id, $response) {
    if (is_wp_error($response)) {
        error_log("Failed to send WhatsApp message: " . $response->get_error_message());
    } else {
        error_log("WhatsApp message sent successfully");
    }
}
```

## Validation

### Add Custom Validation Rules

```php
add_filter('wfbp_form_validation_errors', 'custom_validation', 10, 3);

function custom_validation($errors, $form_data, $form_fields) {
    // Validate age field
    if (isset($form_data['age']) && $form_data['age'] < 18) {
        $errors[] = 'You must be at least 18 years old.';
    }
    
    // Validate custom format
    if (isset($form_data['company_id'])) {
        if (!preg_match('/^[A-Z]{3}\d{6}$/', $form_data['company_id'])) {
            $errors[] = 'Company ID must be in format: ABC123456';
        }
    }
    
    // Cross-field validation
    if (isset($form_data['password']) && isset($form_data['confirm_password'])) {
        if ($form_data['password'] !== $form_data['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        }
    }
    
    return $errors;
}
```

### Block Specific Email Domains

```php
add_filter('wfbp_form_validation_errors', 'block_email_domains', 10, 3);

function block_email_domains($errors, $form_data, $form_fields) {
    if (isset($form_data['email'])) {
        $blocked_domains = array('tempmail.com', 'throwaway.email', 'guerrillamail.com');
        $email_parts = explode('@', $form_data['email']);
        
        if (isset($email_parts[1]) && in_array($email_parts[1], $blocked_domains)) {
            $errors[] = 'Please use a valid email address.';
        }
    }
    
    return $errors;
}
```

## Webhooks

### Customize Webhook Data

```php
add_filter('wfbp_webhook_data', 'customize_webhook_payload', 10, 3);

function customize_webhook_payload($webhook_data, $form_data, $lead_id) {
    // Add custom fields
    $webhook_data['environment'] = wp_get_environment_type();
    $webhook_data['site_name'] = get_bloginfo('name');
    $webhook_data['site_url'] = get_site_url();
    
    // Add user information if logged in
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $webhook_data['submitted_by'] = array(
            'user_id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
        );
    }
    
    // Add custom metadata
    $webhook_data['metadata'] = array(
        'server_time' => current_time('mysql'),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'referrer' => wp_get_referer(),
    );
    
    return $webhook_data;
}
```

## Plugin Activation/Deactivation

### Run Custom Code on Activation

```php
add_action('wfbp_after_activation', 'custom_activation_tasks');

function custom_activation_tasks() {
    // Create custom options
    add_option('my_custom_wfbp_option', 'default_value');
    
    // Create custom database table
    global $wpdb;
    $table_name = $wpdb->prefix . 'my_custom_data';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        data TEXT,
        PRIMARY KEY (id)
    )";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Schedule custom cron job
    if (!wp_next_scheduled('my_custom_daily_task')) {
        wp_schedule_event(time(), 'daily', 'my_custom_daily_task');
    }
}
```

### Run Custom Code on Deactivation

```php
add_action('wfbp_after_deactivation', 'custom_deactivation_tasks');

function custom_deactivation_tasks() {
    // Clear scheduled tasks
    wp_clear_scheduled_hook('my_custom_daily_task');
    
    // Optionally clean up options (be careful with this)
    // delete_option('my_custom_wfbp_option');
}
```

## Advanced Examples

### Integrate with Google Analytics

```php
add_action('wfbp_after_save_lead', 'track_in_google_analytics', 10, 3);

function track_in_google_analytics($lead_id, $form_data, $form_id) {
    // Use Measurement Protocol API
    $tracking_id = 'UA-XXXXX-Y'; // Your GA tracking ID
    $client_id = isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : '';
    
    wp_remote_post('https://www.google-analytics.com/collect', array(
        'body' => array(
            'v' => 1,
            't' => 'event',
            'tid' => $tracking_id,
            'cid' => $client_id,
            'ec' => 'WhatsApp Form',
            'ea' => 'Submit',
            'el' => 'Form ID: ' . $form_id,
            'ev' => 1,
        ),
    ));
}
```

### Add reCAPTCHA Score Threshold

```php
add_filter('wfbp_form_validation_errors', 'check_recaptcha_score', 10, 3);

function check_recaptcha_score($errors, $form_data, $form_fields) {
    // This is an example - you'd need to implement reCAPTCHA v3 score checking
    $recaptcha_score = 0.5; // Get actual score from verification
    $threshold = 0.5;
    
    if ($recaptcha_score < $threshold) {
        $errors[] = 'Bot detection: Please verify you are human.';
    }
    
    return $errors;
}
```

### Create Custom Field Type

```php
// This would require modifications to the core plugin
// But shows how you might extend functionality

add_filter('wfbp_field_types', 'add_custom_field_type');

function add_custom_field_type($field_types) {
    $field_types['color'] = __('Color Picker', 'my-theme');
    return $field_types;
}

add_filter('wfbp_render_field', 'render_custom_field_type', 10, 2);

function render_custom_field_type($html, $field) {
    if ($field['type'] === 'color') {
        $html = '<input type="color" name="' . esc_attr($field['name']) . '" />';
    }
    return $html;
}
```

## Tips

1. **Always sanitize and validate** user input in your custom functions
2. **Use WordPress functions** when possible (like `wp_remote_post`, `sanitize_text_field`, etc.)
3. **Handle errors gracefully** and log them for debugging
4. **Test thoroughly** before deploying to production
5. **Document your customizations** for future reference

## Need More Help?

- Check the [README.md](README.md) for plugin documentation
- Review WordPress [Plugin API](https://developer.wordpress.org/plugins/hooks/)
- Open an issue on [GitHub](https://github.com/hindiadobe-png/whatsapp-form-builder/issues)
