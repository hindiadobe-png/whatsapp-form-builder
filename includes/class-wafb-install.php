<?php
/**
 * Installation and database setup
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Install Class
 */
class WAFB_Install {
    
    /**
     * Plugin activation
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        
        // Add flush rewrite rules flag
        set_transient('wafb_flush_rewrite_rules', true, 60);
        
        // Set activation time
        if (!get_option('wafb_activated_time')) {
            update_option('wafb_activated_time', time());
        }
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wafb_cleanup_old_leads');
    }
    
    /**
     * Create plugin tables
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Forms table
        $forms_table = $wpdb->prefix . 'wafb_forms';
        $forms_sql = "CREATE TABLE IF NOT EXISTS {$forms_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            fields longtext NOT NULL,
            settings longtext NOT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status)
        ) {$charset_collate};";
        
        // Leads table
        $leads_table = $wpdb->prefix . 'wafb_leads';
        $leads_sql = "CREATE TABLE IF NOT EXISTS {$leads_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            data longtext NOT NULL,
            page_url varchar(500),
            user_agent varchar(500),
            ip_address varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY form_id (form_id),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($forms_sql);
        dbDelta($leads_sql);
        
        update_option('wafb_db_version', WAFB_VERSION);
    }
    
    /**
     * Set default options
     */
    private static function set_default_options() {
        $defaults = array(
            'wafb_recaptcha_enabled' => false,
            'wafb_recaptcha_site_key' => '',
            'wafb_recaptcha_secret_key' => '',
            'wafb_save_leads' => true,
            'wafb_license_key' => '',
            'wafb_license_status' => 'free',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
}
