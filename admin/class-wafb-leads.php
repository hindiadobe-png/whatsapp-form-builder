<?php
/**
 * Leads handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Leads Class
 */
class WAFB_Leads {
    
    /**
     * Get leads
     */
    public static function get_leads($form_id = 0, $limit = 100, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_leads';
        
        $where = '';
        if ($form_id > 0) {
            $where = $wpdb->prepare(" WHERE form_id = %d", $form_id);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}{$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            )
        );
    }
    
    /**
     * Get leads count
     */
    public static function get_leads_count($form_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_leads';
        
        $where = '';
        if ($form_id > 0) {
            $where = $wpdb->prepare(" WHERE form_id = %d", $form_id);
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table}{$where}");
    }
}
