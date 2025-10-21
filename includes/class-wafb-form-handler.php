<?php
/**
 * Form submission handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Form_Handler Class
 */
class WAFB_Form_Handler {
    
    /**
     * Handle form submission
     */
    public static function handle_submission() {
        // Verify nonce
        if (!isset($_POST['wafb_nonce']) || !wp_verify_nonce($_POST['wafb_nonce'], 'wafb_form_submit')) {
            wp_send_json_error(array('message' => __('Security check failed', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        if ($form_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid form', 'whatsapp-form-builder')));
        }
        
        // Get form
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $form_id), ARRAY_A);
        
        if (!$form) {
            wp_send_json_error(array('message' => __('Form not found', 'whatsapp-form-builder')));
        }
        
        $fields = json_decode($form['fields'], true);
        $settings = json_decode($form['settings'], true);
        
        // Verify reCAPTCHA if enabled
        if (!empty($settings['recaptcha_enabled']) && $settings['recaptcha_enabled']) {
            if (!self::verify_recaptcha()) {
                wp_send_json_error(array('message' => __('reCAPTCHA verification failed', 'whatsapp-form-builder')));
            }
        }
        
        // Validate and sanitize form data
        $form_data = array();
        $errors = array();
        
        foreach ($fields as $field) {
            $field_name = $field['name'];
            $field_label = $field['label'];
            $field_required = isset($field['required']) && $field['required'];
            $field_type = $field['type'];
            
            $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
            
            // Check required fields
            if ($field_required && empty($value)) {
                $errors[] = sprintf(__('%s is required', 'whatsapp-form-builder'), $field_label);
                continue;
            }
            
            // Sanitize based on field type
            switch ($field_type) {
                case 'email':
                    $value = sanitize_email($value);
                    if (!empty($value) && !is_email($value)) {
                        $errors[] = sprintf(__('%s must be a valid email', 'whatsapp-form-builder'), $field_label);
                    }
                    break;
                case 'phone':
                    $value = sanitize_text_field($value);
                    if (!empty($value) && !preg_match('/^[+]?[0-9\s\-\(\)]+$/', $value)) {
                        $errors[] = sprintf(__('%s must be a valid phone number', 'whatsapp-form-builder'), $field_label);
                    }
                    break;
                case 'url':
                    $value = esc_url_raw($value);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                default:
                    $value = sanitize_text_field($value);
                    break;
            }
            
            $form_data[$field_name] = array(
                'label' => $field_label,
                'value' => $value
            );
        }
        
        if (!empty($errors)) {
            wp_send_json_error(array('message' => implode('<br>', $errors)));
        }
        
        // Save lead if enabled
        if (get_option('wafb_save_leads', true)) {
            self::save_lead($form_id, $form_data);
        }
        
        // Send webhook if enabled (Pro feature)
        if (!empty($settings['webhook_enabled']) && !empty($settings['webhook_url'])) {
            if (get_option('wafb_license_status') === 'active') {
                self::send_webhook($settings['webhook_url'], $form_data, $form_id);
            }
        }
        
        // Generate WhatsApp message
        $whatsapp_url = self::generate_whatsapp_url($form_data, $settings);
        
        // Send auto-response if enabled (Pro feature)
        if (!empty($settings['auto_response_enabled']) && !empty($settings['auto_response_message'])) {
            if (get_option('wafb_license_status') === 'active') {
                self::send_auto_response($form_data, $settings);
            }
        }
        
        wp_send_json_success(array(
            'message' => __('Form submitted successfully', 'whatsapp-form-builder'),
            'whatsapp_url' => $whatsapp_url
        ));
    }
    
    /**
     * Verify reCAPTCHA
     */
    private static function verify_recaptcha() {
        if (!isset($_POST['g-recaptcha-response'])) {
            return false;
        }
        
        $secret_key = get_option('wafb_recaptcha_secret_key');
        if (empty($secret_key)) {
            return false;
        }
        
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $secret_key,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        return isset($result['success']) && $result['success'];
    }
    
    /**
     * Save lead to database
     */
    private static function save_lead($form_id, $form_data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_leads';
        
        $wpdb->insert($table, array(
            'form_id' => $form_id,
            'data' => json_encode($form_data),
            'page_url' => isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'ip_address' => self::get_user_ip()
        ));
    }
    
    /**
     * Get user IP address
     */
    private static function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Generate WhatsApp URL
     */
    private static function generate_whatsapp_url($form_data, $settings) {
        // Get WhatsApp number(s)
        $phone_number = !empty($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '';
        
        // Support multiple numbers (Pro feature)
        if (!empty($settings['multiple_numbers']) && get_option('wafb_license_status') === 'active') {
            $numbers = explode(',', $phone_number);
            $phone_number = trim($numbers[0]); // Use first number for now
        }
        
        // Clean phone number
        $phone_number = preg_replace('/[^0-9+]/', '', $phone_number);
        
        // Build message
        $message = self::build_message($form_data, $settings);
        
        // Generate URL
        $whatsapp_url = 'https://wa.me/' . $phone_number . '?text=' . urlencode($message);
        
        return $whatsapp_url;
    }
    
    /**
     * Build WhatsApp message
     */
    private static function build_message($form_data, $settings) {
        $message = '';
        
        // Use custom template if available (Pro feature)
        if (!empty($settings['message_template']) && get_option('wafb_license_status') === 'active') {
            $message = $settings['message_template'];
            
            // Replace placeholders
            foreach ($form_data as $field_name => $field_data) {
                $message = str_replace('{' . $field_name . '}', $field_data['value'], $message);
            }
            
            // Replace page URL
            $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
            $message = str_replace('{page_url}', $page_url, $message);
        } else {
            // Default message format
            foreach ($form_data as $field_name => $field_data) {
                if (!empty($field_data['value'])) {
                    $message .= '*' . $field_data['label'] . ':* ' . $field_data['value'] . "\n";
                }
            }
            
            // Add page URL
            $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
            if (!empty($page_url)) {
                $message .= "\n*" . __('Page', 'whatsapp-form-builder') . ':* ' . $page_url;
            }
        }
        
        return $message;
    }
    
    /**
     * Send webhook (Pro feature)
     */
    private static function send_webhook($webhook_url, $form_data, $form_id) {
        $payload = array(
            'form_id' => $form_id,
            'data' => $form_data,
            'page_url' => isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '',
            'timestamp' => current_time('mysql')
        );
        
        wp_remote_post($webhook_url, array(
            'body' => json_encode($payload),
            'headers' => array('Content-Type' => 'application/json')
        ));
    }
    
    /**
     * Send auto-response (Pro feature)
     */
    private static function send_auto_response($form_data, $settings) {
        // Get email from form data
        $email = '';
        foreach ($form_data as $field_name => $field_data) {
            if (strpos($field_name, 'email') !== false && is_email($field_data['value'])) {
                $email = $field_data['value'];
                break;
            }
        }
        
        if (empty($email)) {
            return;
        }
        
        $subject = !empty($settings['auto_response_subject']) ? $settings['auto_response_subject'] : __('Thank you for your submission', 'whatsapp-form-builder');
        $message = !empty($settings['auto_response_message']) ? $settings['auto_response_message'] : '';
        
        wp_mail($email, $subject, $message);
    }
}
