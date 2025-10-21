<?php
/**
 * WhatsApp integration class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_WhatsApp {
    
    /**
     * Generate WhatsApp message URL
     */
    public static function generate_whatsapp_url($phone_number, $message) {
        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Encode message
        $encoded_message = rawurlencode($message);
        
        // Apply filter
        $encoded_message = apply_filters('wfbp_whatsapp_message', $encoded_message, $phone);
        
        // Generate URL
        $url = 'https://wa.me/' . $phone . '?text=' . $encoded_message;
        
        return $url;
    }
    
    /**
     * Format form data as WhatsApp message
     */
    public static function format_message($form_data, $form_settings, $page_url = '') {
        $message = '';
        
        // Message template
        $template = isset($form_settings['message_template']) ? $form_settings['message_template'] : '';
        
        if ($template) {
            // Use custom template (Pro feature)
            $message = self::apply_template($template, $form_data, $page_url);
        } else {
            // Default message format
            $message = self::format_default_message($form_data, $page_url);
        }
        
        // Apply filter
        $message = apply_filters('wfbp_format_message', $message, $form_data, $form_settings);
        
        return $message;
    }
    
    /**
     * Format default message
     */
    private static function format_default_message($form_data, $page_url = '') {
        $message = "New Form Submission\n\n";
        
        foreach ($form_data as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));
            $message .= "*{$label}:* {$value}\n";
        }
        
        if ($page_url) {
            $message .= "\n*Page URL:* {$page_url}";
        }
        
        return $message;
    }
    
    /**
     * Apply custom template (Pro feature)
     */
    private static function apply_template($template, $form_data, $page_url = '') {
        // Replace placeholders with actual data
        $message = $template;
        
        foreach ($form_data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        $message = str_replace('{page_url}', $page_url, $message);
        
        return $message;
    }
    
    /**
     * Send via WhatsApp Business API (Pro feature)
     */
    public static function send_via_business_api($phone_number, $message, $form_id) {
        // Check if pro is enabled
        if (!self::is_pro_enabled()) {
            return false;
        }
        
        // Apply filter before sending
        do_action('wfbp_before_send_message', $phone_number, $message, $form_id);
        
        // Get Business API credentials
        $api_key = get_option('wfbp_business_api_key', '');
        $api_url = get_option('wfbp_business_api_url', '');
        
        if (!$api_key || !$api_url) {
            return false;
        }
        
        // Prepare API request
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'phone' => $phone_number,
                'message' => $message,
            )),
            'timeout' => 30,
        ));
        
        // Log the result
        self::log_api_call($form_id, $response);
        
        // Apply action after sending
        do_action('wfbp_after_send_message', $phone_number, $message, $form_id, $response);
        
        return !is_wp_error($response);
    }
    
    /**
     * Log API call
     */
    private static function log_api_call($form_id, $response) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_logs';
        
        $status = is_wp_error($response) ? 'error' : 'success';
        $message = is_wp_error($response) ? $response->get_error_message() : 'API call successful';
        
        $wpdb->insert(
            $table_name,
            array(
                'lead_id' => null,
                'action' => 'business_api_call',
                'message' => $message,
                'status' => $status,
            ),
            array('%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Check if pro version is enabled
     */
    private static function is_pro_enabled() {
        return get_option('wfbp_pro_enabled', false) && get_option('wfbp_license_status', 'inactive') === 'active';
    }
    
    /**
     * Send auto-response (Pro feature)
     */
    public static function send_auto_response($email, $form_data, $form_settings) {
        if (!self::is_pro_enabled()) {
            return false;
        }
        
        if (!isset($form_settings['enable_auto_response']) || !$form_settings['enable_auto_response']) {
            return false;
        }
        
        $subject = isset($form_settings['auto_response_subject']) ? $form_settings['auto_response_subject'] : __('Thank you for your submission', 'whatsapp-form-builder');
        $message = isset($form_settings['auto_response_message']) ? $form_settings['auto_response_message'] : __('We have received your message and will get back to you soon.', 'whatsapp-form-builder');
        
        // Send email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Trigger webhook (Pro feature)
     */
    public static function trigger_webhook($form_data, $form_settings, $lead_id) {
        if (!self::is_pro_enabled()) {
            return false;
        }
        
        if (!isset($form_settings['webhook_url']) || empty($form_settings['webhook_url'])) {
            return false;
        }
        
        $webhook_url = $form_settings['webhook_url'];
        
        // Prepare webhook data
        $webhook_data = array(
            'lead_id' => $lead_id,
            'form_data' => $form_data,
            'timestamp' => current_time('mysql'),
        );
        
        // Apply filter
        $webhook_data = apply_filters('wfbp_webhook_data', $webhook_data, $form_data, $lead_id);
        
        // Send webhook
        $response = wp_remote_post($webhook_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($webhook_data),
            'timeout' => 30,
        ));
        
        return !is_wp_error($response);
    }
}
