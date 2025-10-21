<?php
/**
 * Fired during plugin deactivation
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Deactivator {
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Hook for post-deactivation
        do_action('wfbp_after_deactivation');
    }
}
