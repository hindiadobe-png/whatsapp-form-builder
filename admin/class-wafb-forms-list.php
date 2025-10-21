<?php
/**
 * Forms list handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Forms_List Class
 */
class WAFB_Forms_List {
    
    /**
     * Get all forms
     */
    public static function get_forms() {
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");
    }
}
