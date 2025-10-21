<?php
/**
 * Fired during plugin deactivation
 *
 * @package WhatsApp_Form_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class WAFB_Deactivator {

    /**
     * Plugin deactivation logic.
     *
     * Clean up temporary data if needed.
     */
    public static function deactivate() {
        // Clean up scheduled tasks if any
        delete_option( 'wafb_activated' );
    }
}
