<?php
/**
 * Admin view: Settings
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('WhatsApp Form Builder Settings', 'whatsapp-form-builder'); ?></h1>
    
    <?php settings_errors('wfbp_settings'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wfbp_save_settings', 'wfbp_settings_nonce'); ?>
        
        <h2><?php esc_html_e('reCAPTCHA Settings', 'whatsapp-form-builder'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="recaptcha_site_key"><?php esc_html_e('reCAPTCHA Site Key', 'whatsapp-form-builder'); ?></label>
                </th>
                <td>
                    <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" class="regular-text">
                    <p class="description">
                        <?php 
                        printf(
                            esc_html__('Get your reCAPTCHA keys from %s', 'whatsapp-form-builder'),
                            '<a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a>'
                        ); 
                        ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="recaptcha_secret_key"><?php esc_html_e('reCAPTCHA Secret Key', 'whatsapp-form-builder'); ?></label>
                </th>
                <td>
                    <input type="text" id="recaptcha_secret_key" name="recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        
        <?php if (WFBP_License::is_pro_enabled()): ?>
            <h2><?php esc_html_e('WhatsApp Business API Settings', 'whatsapp-form-builder'); ?> <span class="wfbp-pro-badge"><?php esc_html_e('PRO', 'whatsapp-form-builder'); ?></span></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="business_api_key"><?php esc_html_e('API Key', 'whatsapp-form-builder'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="business_api_key" name="business_api_key" value="<?php echo esc_attr($settings['business_api_key']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="business_api_url"><?php esc_html_e('API URL', 'whatsapp-form-builder'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="business_api_url" name="business_api_url" value="<?php echo esc_url($settings['business_api_url']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
        <?php endif; ?>
        
        <h2><?php esc_html_e('Notification Settings', 'whatsapp-form-builder'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_lead_notifications"><?php esc_html_e('Email Notifications', 'whatsapp-form-builder'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="enable_lead_notifications" name="enable_lead_notifications" <?php checked($settings['enable_lead_notifications']); ?>>
                        <?php esc_html_e('Send email notification when a new lead is submitted', 'whatsapp-form-builder'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="notification_email"><?php esc_html_e('Notification Email', 'whatsapp-form-builder'); ?></label>
                </th>
                <td>
                    <input type="email" id="notification_email" name="notification_email" value="<?php echo esc_attr($settings['notification_email']); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'whatsapp-form-builder'), 'primary', 'wfbp_save_settings'); ?>
    </form>
</div>
