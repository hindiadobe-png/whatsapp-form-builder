<?php
/**
 * Admin settings page
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Admin_Settings {
    
    /**
     * Render settings page
     */
    public function render() {
        // Handle form submission
        if (isset($_POST['wfbp_save_settings'])) {
            $this->save_settings();
        }
        
        // Get current settings
        $settings = $this->get_settings();
        
        include_once WFBP_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Get current settings
     */
    private function get_settings() {
        return array(
            'recaptcha_site_key' => get_option('wfbp_recaptcha_site_key', ''),
            'recaptcha_secret_key' => get_option('wfbp_recaptcha_secret_key', ''),
            'business_api_key' => get_option('wfbp_business_api_key', ''),
            'business_api_url' => get_option('wfbp_business_api_url', ''),
            'enable_lead_notifications' => get_option('wfbp_enable_lead_notifications', false),
            'notification_email' => get_option('wfbp_notification_email', get_option('admin_email')),
        );
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Check nonce
        if (!isset($_POST['wfbp_settings_nonce']) || !wp_verify_nonce($_POST['wfbp_settings_nonce'], 'wfbp_save_settings')) {
            wp_die(__('Security check failed.', 'whatsapp-form-builder'));
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'whatsapp-form-builder'));
        }
        
        // Save reCAPTCHA settings
        update_option('wfbp_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
        update_option('wfbp_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
        
        // Save Business API settings (Pro feature)
        if (WFBP_License::is_pro_enabled()) {
            update_option('wfbp_business_api_key', sanitize_text_field($_POST['business_api_key']));
            update_option('wfbp_business_api_url', esc_url_raw($_POST['business_api_url']));
        }
        
        // Save notification settings
        update_option('wfbp_enable_lead_notifications', isset($_POST['enable_lead_notifications']));
        update_option('wfbp_notification_email', sanitize_email($_POST['notification_email']));
        
        // Show success message
        add_settings_error('wfbp_settings', 'settings_saved', __('Settings saved successfully!', 'whatsapp-form-builder'), 'success');
    }
}
