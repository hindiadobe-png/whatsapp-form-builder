<?php
/**
 * Uninstall script
 * Fired when the plugin is uninstalled.
 *
 * @package WhatsApp_Form_Builder
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove plugin data
 */
function wafb_uninstall() {
    global $wpdb;
    
    // Delete options
    $options = array(
        'wafb_version',
        'wafb_db_version',
        'wafb_activated_time',
        'wafb_recaptcha_enabled',
        'wafb_recaptcha_site_key',
        'wafb_recaptcha_secret_key',
        'wafb_save_leads',
        'wafb_license_key',
        'wafb_license_status',
        'wafb_license_expires',
    );
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Drop custom tables
    $forms_table = $wpdb->prefix . 'wafb_forms';
    $leads_table = $wpdb->prefix . 'wafb_leads';
    
    $wpdb->query("DROP TABLE IF EXISTS {$forms_table}");
    $wpdb->query("DROP TABLE IF EXISTS {$leads_table}");
    
    // Clear scheduled events
    wp_clear_scheduled_hook('wafb_daily_license_check');
    wp_clear_scheduled_hook('wafb_cleanup_old_leads');
    
    // Clear any transients
    delete_transient('wafb_flush_rewrite_rules');
}

// Run uninstall
wafb_uninstall();
