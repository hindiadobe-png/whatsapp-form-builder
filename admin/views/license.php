<?php
/**
 * Admin view: License
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle license activation/deactivation
if (isset($_POST['wfbp_activate_license'])) {
    check_admin_referer('wfbp_license_action', 'wfbp_license_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'whatsapp-form-builder'));
    }
    
    $license_key = sanitize_text_field($_POST['license_key']);
    $result = WFBP_License::activate_license($license_key);
    
    if ($result['success']) {
        add_settings_error('wfbp_license', 'license_activated', $result['message'], 'success');
    } else {
        add_settings_error('wfbp_license', 'license_error', $result['message'], 'error');
    }
}

if (isset($_POST['wfbp_deactivate_license'])) {
    check_admin_referer('wfbp_license_action', 'wfbp_license_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'whatsapp-form-builder'));
    }
    
    $result = WFBP_License::deactivate_license();
    add_settings_error('wfbp_license', 'license_deactivated', $result['message'], 'success');
}

$license_info = WFBP_License::get_license_info();
?>

<div class="wrap">
    <h1><?php esc_html_e('License Management', 'whatsapp-form-builder'); ?></h1>
    
    <?php settings_errors('wfbp_license'); ?>
    
    <div class="wfbp-license-container">
        <?php if ($license_info['status'] === 'active'): ?>
            <div class="notice notice-success inline">
                <p><strong><?php esc_html_e('Your Pro license is active!', 'whatsapp-form-builder'); ?></strong></p>
            </div>
            
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('License Key', 'whatsapp-form-builder'); ?></th>
                    <td><code><?php echo esc_html($license_info['license_key']); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Status', 'whatsapp-form-builder'); ?></th>
                    <td><span class="wfbp-status-active"><?php esc_html_e('Active', 'whatsapp-form-builder'); ?></span></td>
                </tr>
                <?php if ($license_info['expires']): ?>
                    <tr>
                        <th><?php esc_html_e('Expires', 'whatsapp-form-builder'); ?></th>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license_info['expires']))); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
            
            <form method="post" action="">
                <?php wp_nonce_field('wfbp_license_action', 'wfbp_license_nonce'); ?>
                <p>
                    <button type="submit" name="wfbp_deactivate_license" class="button button-secondary">
                        <?php esc_html_e('Deactivate License', 'whatsapp-form-builder'); ?>
                    </button>
                </p>
            </form>
        <?php else: ?>
            <div class="notice notice-warning inline">
                <p><?php esc_html_e('Enter your license key to unlock Pro features.', 'whatsapp-form-builder'); ?></p>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('wfbp_license_action', 'wfbp_license_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="license_key"><?php esc_html_e('License Key', 'whatsapp-form-builder'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="license_key" name="license_key" value="<?php echo esc_attr($license_info['license_key']); ?>" class="regular-text" placeholder="xxxx-xxxx-xxxx-xxxx">
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="wfbp_activate_license" class="button button-primary">
                        <?php esc_html_e('Activate License', 'whatsapp-form-builder'); ?>
                    </button>
                </p>
            </form>
            
            <div class="wfbp-pro-features">
                <h2><?php esc_html_e('Pro Features', 'whatsapp-form-builder'); ?></h2>
                <ul>
                    <li>✓ <?php esc_html_e('Advanced message templates with custom placeholders', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Multiple recipient phone numbers', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('WhatsApp Business API integration', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Lead export to CSV', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Webhook integration for external services', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Auto-response email to users', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Advanced custom design options', 'whatsapp-form-builder'); ?></li>
                    <li>✓ <?php esc_html_e('Priority support', 'whatsapp-form-builder'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
