<?php
/**
 * Fired during plugin activation
 *
 * @package WhatsApp_Form_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class WAFB_Activator {

    /**
     * Plugin activation logic.
     *
     * Create necessary database tables and set default options.
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = $wpdb->prefix . 'wafb_forms';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            form_data longtext NOT NULL,
            form_settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Set default options
        add_option( 'wafb_version', WAFB_VERSION );
        add_option( 'wafb_activated', true );
    }
}
