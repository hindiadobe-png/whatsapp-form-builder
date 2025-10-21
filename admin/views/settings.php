<?php
/**
 * Settings view
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

// Save settings
if (isset($_POST['wafb_save_settings']) && check_admin_referer('wafb_settings', 'wafb_settings_nonce')) {
    $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'general';
    
    if ($tab === 'general') {
        update_option('wafb_save_leads', isset($_POST['save_leads']));
    } elseif ($tab === 'recaptcha') {
        update_option('wafb_recaptcha_enabled', isset($_POST['recaptcha_enabled']));
        update_option('wafb_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
        update_option('wafb_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
    } elseif ($tab === 'license') {
        $action = isset($_POST['license_action']) ? $_POST['license_action'] : '';
        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
        
        $license_handler = new WAFB_License();
        
        if ($action === 'activate' && !empty($license_key)) {
            $result = $license_handler->activate_license($license_key);
            $license_message = $result['message'];
            $license_success = $result['success'];
        } elseif ($action === 'deactivate') {
            $result = $license_handler->deactivate_license();
            $license_message = $result['message'];
            $license_success = $result['success'];
        }
    }
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved.', 'whatsapp-form-builder') . '</p></div>';
}

if (isset($license_message)) {
    $notice_class = $license_success ? 'notice-success' : 'notice-error';
    echo '<div class="notice ' . $notice_class . '"><p>' . esc_html($license_message) . '</p></div>';
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
$tabs = WAFB_Settings::get_tabs();
?>

<div class="wrap wafb-admin-page">
    <h1><?php _e('Settings', 'whatsapp-form-builder'); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_label): ?>
            <a href="<?php echo admin_url('admin.php?page=wafb-settings&tab=' . $tab_key); ?>" class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </h2>
    
    <form method="post" action="">
        <?php wp_nonce_field('wafb_settings', 'wafb_settings_nonce'); ?>
        <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>">
        
        <?php if ($active_tab === 'general'): ?>
            <table class="form-table">
                <tr>
                    <th><?php _e('Save Leads', 'whatsapp-form-builder'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="save_leads" value="1" <?php checked(get_option('wafb_save_leads', true)); ?>>
                            <?php _e('Save form submissions to database', 'whatsapp-form-builder'); ?>
                        </label>
                        <p class="description"><?php _e('When enabled, all form submissions will be saved and can be viewed in the Leads section.', 'whatsapp-form-builder'); ?></p>
                    </td>
                </tr>
            </table>
            
        <?php elseif ($active_tab === 'recaptcha'): ?>
            <table class="form-table">
                <tr>
                    <th><?php _e('Enable reCAPTCHA', 'whatsapp-form-builder'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(get_option('wafb_recaptcha_enabled')); ?>>
                            <?php _e('Enable reCAPTCHA globally', 'whatsapp-form-builder'); ?>
                        </label>
                        <p class="description">
                            <?php printf(__('Get your reCAPTCHA keys from <a href="%s" target="_blank">Google reCAPTCHA</a>', 'whatsapp-form-builder'), 'https://www.google.com/recaptcha/admin'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Site Key', 'whatsapp-form-builder'); ?></th>
                    <td>
                        <input type="text" name="recaptcha_site_key" class="regular-text" value="<?php echo esc_attr(get_option('wafb_recaptcha_site_key')); ?>">
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Secret Key', 'whatsapp-form-builder'); ?></th>
                    <td>
                        <input type="text" name="recaptcha_secret_key" class="regular-text" value="<?php echo esc_attr(get_option('wafb_recaptcha_secret_key')); ?>">
                    </td>
                </tr>
            </table>
            
        <?php elseif ($active_tab === 'license'): ?>
            <?php
            $license_status = get_option('wafb_license_status', 'free');
            $license_key = get_option('wafb_license_key', '');
            $license_expires = get_option('wafb_license_expires', '');
            ?>
            
            <table class="form-table">
                <tr>
                    <th><?php _e('License Status', 'whatsapp-form-builder'); ?></th>
                    <td>
                        <?php if ($license_status === 'active'): ?>
                            <span class="wafb-license-status wafb-license-active">
                                <span class="dashicons dashicons-yes"></span>
                                <?php _e('Active', 'whatsapp-form-builder'); ?>
                            </span>
                            <?php if (!empty($license_expires)): ?>
                                <p class="description">
                                    <?php printf(__('Expires: %s', 'whatsapp-form-builder'), date_i18n(get_option('date_format'), strtotime($license_expires))); ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="wafb-license-status wafb-license-free">
                                <?php _e('Free Version', 'whatsapp-form-builder'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <?php if ($license_status === 'active'): ?>
                    <tr>
                        <th><?php _e('License Key', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" value="<?php echo esc_attr(str_repeat('*', 20) . substr($license_key, -4)); ?>" class="regular-text" disabled>
                            <input type="hidden" name="license_action" value="deactivate">
                            <p class="description">
                                <button type="submit" class="button"><?php _e('Deactivate License', 'whatsapp-form-builder'); ?></button>
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th><?php _e('License Key', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" name="license_key" class="regular-text" value="<?php echo esc_attr($license_key); ?>" placeholder="<?php _e('Enter your license key', 'whatsapp-form-builder'); ?>">
                            <input type="hidden" name="license_action" value="activate">
                            <p class="description">
                                <?php _e('Enter your license key to activate Pro features.', 'whatsapp-form-builder'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Pro Features', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <ul style="list-style: disc; margin-left: 20px;">
                                <li><?php _e('Advanced message templates with custom placeholders', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Multiple recipient WhatsApp numbers', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('WhatsApp Business API integration', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Lead saving and exporting to CSV', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Webhook integration for external services', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Auto-response emails to users', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Custom design options and CSS', 'whatsapp-form-builder'); ?></li>
                                <li><?php _e('Priority support', 'whatsapp-form-builder'); ?></li>
                            </ul>
                            <p>
                                <a href="https://example.com/pro" class="button button-primary" target="_blank">
                                    <?php _e('Get Pro License', 'whatsapp-form-builder'); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>
        
        <p class="submit">
            <input type="submit" name="wafb_save_settings" class="button button-primary" value="<?php _e('Save Changes', 'whatsapp-form-builder'); ?>">
        </p>
    </form>
</div>
