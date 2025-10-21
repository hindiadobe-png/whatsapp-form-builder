<?php
/**
 * Fired during plugin activation
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Activator {
    
    /**
     * Plugin activation
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create leads table
        $leads_table = $wpdb->prefix . 'wfbp_leads';
        $leads_sql = "CREATE TABLE IF NOT EXISTS $leads_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id bigint(20) UNSIGNED NOT NULL,
            form_data longtext NOT NULL,
            page_url varchar(255) NOT NULL,
            user_ip varchar(100) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Create logs table
        $logs_table = $wpdb->prefix . 'wfbp_logs';
        $logs_sql = "CREATE TABLE IF NOT EXISTS $logs_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) UNSIGNED DEFAULT NULL,
            action varchar(100) NOT NULL,
            message text DEFAULT NULL,
            status varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Create forms table
        $forms_table = $wpdb->prefix . 'wfbp_forms';
        $forms_sql = "CREATE TABLE IF NOT EXISTS $forms_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            form_fields longtext NOT NULL,
            form_settings longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($leads_sql);
        dbDelta($logs_sql);
        dbDelta($forms_sql);
        
        // Set default options
        add_option('wfbp_version', WFBP_VERSION);
        add_option('wfbp_pro_enabled', false);
        add_option('wfbp_license_key', '');
        add_option('wfbp_license_status', 'inactive');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Hook for post-activation
        do_action('wfbp_after_activation');
    }
}
