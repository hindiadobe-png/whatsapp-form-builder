<?php
/**
 * Form validation class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Validator {
    
    /**
     * Validate form submission
     */
    public static function validate_form_submission($form_data, $form_fields) {
        $errors = array();
        
        // Validate each field
        foreach ($form_fields as $field) {
            $field_name = isset($field['name']) ? $field['name'] : '';
            $field_type = isset($field['type']) ? $field['type'] : 'text';
            $field_required = isset($field['required']) && $field['required'];
            $field_label = isset($field['label']) ? $field['label'] : $field_name;
            
            $value = isset($form_data[$field_name]) ? $form_data[$field_name] : '';
            
            // Required field check
            if ($field_required && empty($value)) {
                $errors[] = sprintf(__('%s is required.', 'whatsapp-form-builder'), $field_label);
                continue;
            }
            
            // Type-specific validation
            if (!empty($value)) {
                switch ($field_type) {
                    case 'email':
                        if (!self::validate_email($value)) {
                            $errors[] = sprintf(__('%s is not a valid email address.', 'whatsapp-form-builder'), $field_label);
                        }
                        break;
                    case 'phone':
                    case 'tel':
                        if (!self::validate_phone($value)) {
                            $errors[] = sprintf(__('%s is not a valid phone number.', 'whatsapp-form-builder'), $field_label);
                        }
                        break;
                    case 'url':
                        if (!self::validate_url($value)) {
                            $errors[] = sprintf(__('%s is not a valid URL.', 'whatsapp-form-builder'), $field_label);
                        }
                        break;
                    case 'number':
                        if (!is_numeric($value)) {
                            $errors[] = sprintf(__('%s must be a number.', 'whatsapp-form-builder'), $field_label);
                        }
                        break;
                }
            }
            
            // Custom validation patterns
            if (isset($field['pattern']) && !empty($field['pattern']) && !empty($value)) {
                if (!preg_match('/' . $field['pattern'] . '/', $value)) {
                    $pattern_error = isset($field['pattern_error']) ? $field['pattern_error'] : sprintf(__('%s format is invalid.', 'whatsapp-form-builder'), $field_label);
                    $errors[] = $pattern_error;
                }
            }
        }
        
        // Apply filter for custom validation
        $errors = apply_filters('wfbp_form_validation_errors', $errors, $form_data, $form_fields);
        
        return $errors;
    }
    
    /**
     * Validate email
     */
    private static function validate_email($email) {
        return is_email($email);
    }
    
    /**
     * Validate phone number
     */
    private static function validate_phone($phone) {
        // Allow + and digits, minimum 10 digits
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen(str_replace('+', '', $cleaned)) >= 10;
    }
    
    /**
     * Validate URL
     */
    private static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Verify reCAPTCHA
     */
    public static function verify_recaptcha($response_token) {
        $secret_key = get_option('wfbp_recaptcha_secret_key', '');
        
        if (empty($secret_key)) {
            return false;
        }
        
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $response = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $secret_key,
                'response' => $response_token,
            ),
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        return isset($result['success']) && $result['success'] === true;
    }
    
    /**
     * Sanitize form data
     */
    public static function sanitize_form_data($form_data) {
        $sanitized = array();
        
        foreach ($form_data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                // Use different sanitization based on field name
                if (strpos($key, 'email') !== false) {
                    $sanitized[$key] = sanitize_email($value);
                } elseif (strpos($key, 'url') !== false) {
                    $sanitized[$key] = esc_url_raw($value);
                } elseif (strpos($key, 'message') !== false || strpos($key, 'address') !== false) {
                    $sanitized[$key] = sanitize_textarea_field($value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }
}
