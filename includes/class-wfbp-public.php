<?php
/**
 * Public-facing functionality
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Public {
    
    /**
     * Plugin version
     */
    private $version;
    
    /**
     * Initialize the class
     */
    public function __construct($version) {
        $this->version = $version;
    }
    
    /**
     * Register styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wfbp-public',
            WFBP_PLUGIN_URL . 'assets/css/public.css',
            array(),
            $this->version,
            'all'
        );
        
        // Add RTL support
        if (is_rtl()) {
            wp_enqueue_style(
                'wfbp-public-rtl',
                WFBP_PLUGIN_URL . 'assets/css/public-rtl.css',
                array('wfbp-public'),
                $this->version,
                'all'
            );
        }
    }
    
    /**
     * Register scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wfbp-public',
            WFBP_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            $this->version,
            true
        );
        
        // Localize script
        wp_localize_script('wfbp-public', 'wfbp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wfbp_public_nonce'),
            'messages' => array(
                'submitting' => __('Submitting...', 'whatsapp-form-builder'),
                'success' => __('Form submitted successfully!', 'whatsapp-form-builder'),
                'error' => __('An error occurred. Please try again.', 'whatsapp-form-builder'),
                'validation_error' => __('Please fix the errors before submitting.', 'whatsapp-form-builder'),
            ),
        ));
        
        // Load reCAPTCHA if enabled
        $recaptcha_site_key = get_option('wfbp_recaptcha_site_key', '');
        if (!empty($recaptcha_site_key)) {
            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js',
                array(),
                null,
                true
            );
        }
    }
    
    /**
     * Handle form submission via AJAX
     */
    public function ajax_submit_form() {
        // Verify nonce
        if (!check_ajax_referer('wfbp_public_nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'whatsapp-form-builder')
            ));
        }
        
        // Get form data
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : array();
        $recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field($_POST['recaptcha_response']) : '';
        
        if (!$form_id) {
            wp_send_json_error(array(
                'message' => __('Invalid form ID.', 'whatsapp-form-builder')
            ));
        }
        
        // Get form
        $form = WFBP_Form::get_form($form_id);
        
        if (!$form) {
            wp_send_json_error(array(
                'message' => __('Form not found.', 'whatsapp-form-builder')
            ));
        }
        
        // Sanitize form data
        $form_data = WFBP_Validator::sanitize_form_data($form_data);
        
        // Validate form data
        $errors = WFBP_Validator::validate_form_submission($form_data, $form->form_fields);
        
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ));
        }
        
        // Verify reCAPTCHA if enabled
        if (isset($form->form_settings['enable_recaptcha']) && $form->form_settings['enable_recaptcha']) {
            if (empty($recaptcha_response) || !WFBP_Validator::verify_recaptcha($recaptcha_response)) {
                wp_send_json_error(array(
                    'message' => __('reCAPTCHA verification failed.', 'whatsapp-form-builder')
                ));
            }
        }
        
        // Get page URL
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        
        // Save lead
        $lead_id = WFBP_Lead::save_lead($form_id, $form_data, $page_url);
        
        // Format WhatsApp message
        $message = WFBP_WhatsApp::format_message($form_data, $form->form_settings, $page_url);
        
        // Get recipient phone number
        $phone_number = isset($form->form_settings['phone_number']) ? $form->form_settings['phone_number'] : '';
        
        if (empty($phone_number)) {
            wp_send_json_error(array(
                'message' => __('No recipient phone number configured.', 'whatsapp-form-builder')
            ));
        }
        
        // Check for multiple recipients (Pro feature)
        $recipients = array($phone_number);
        if (WFBP_License::is_pro_enabled() && isset($form->form_settings['additional_recipients'])) {
            $recipients = array_merge($recipients, $form->form_settings['additional_recipients']);
        }
        
        // Generate WhatsApp URL (for first recipient)
        $whatsapp_url = WFBP_WhatsApp::generate_whatsapp_url($recipients[0], $message);
        
        // Send via Business API if enabled (Pro feature)
        if (WFBP_License::is_pro_enabled() && isset($form->form_settings['use_business_api']) && $form->form_settings['use_business_api']) {
            foreach ($recipients as $recipient) {
                WFBP_WhatsApp::send_via_business_api($recipient, $message, $form_id);
            }
        }
        
        // Trigger webhook (Pro feature)
        if (WFBP_License::is_pro_enabled()) {
            WFBP_WhatsApp::trigger_webhook($form_data, $form->form_settings, $lead_id);
        }
        
        // Send auto-response (Pro feature)
        if (WFBP_License::is_pro_enabled() && isset($form_data['email'])) {
            WFBP_WhatsApp::send_auto_response($form_data['email'], $form_data, $form->form_settings);
        }
        
        // Return success with WhatsApp URL
        wp_send_json_success(array(
            'message' => __('Form submitted successfully!', 'whatsapp-form-builder'),
            'whatsapp_url' => $whatsapp_url,
            'lead_id' => $lead_id
        ));
    }
}
