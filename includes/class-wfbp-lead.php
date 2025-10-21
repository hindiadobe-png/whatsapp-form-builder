<?php
/**
 * Lead management class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Lead {
    
    /**
     * Save lead
     */
    public static function save_lead($form_id, $form_data, $page_url) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        // Get user info
        $user_ip = self::get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        // Apply filter before saving
        $form_data = apply_filters('wfbp_before_save_lead', $form_data, $form_id);
        
        $data = array(
            'form_id' => $form_id,
            'form_data' => wp_json_encode($form_data),
            'page_url' => esc_url_raw($page_url),
            'user_ip' => $user_ip,
            'user_agent' => $user_agent
        );
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        $lead_id = $wpdb->insert_id;
        
        // Apply action after saving
        do_action('wfbp_after_save_lead', $lead_id, $form_data, $form_id);
        
        return $lead_id;
    }
    
    /**
     * Get lead by ID
     */
    public static function get_lead($lead_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $lead_id
        ));
        
        if ($lead) {
            $lead->form_data = json_decode($lead->form_data, true);
        }
        
        return $lead;
    }
    
    /**
     * Get leads by form ID
     */
    public static function get_leads_by_form($form_id, $limit = 50, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $form_id,
            $limit,
            $offset
        ));
        
        foreach ($leads as $lead) {
            $lead->form_data = json_decode($lead->form_data, true);
        }
        
        return $leads;
    }
    
    /**
     * Get all leads
     */
    public static function get_all_leads($limit = 50, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        foreach ($leads as $lead) {
            $lead->form_data = json_decode($lead->form_data, true);
        }
        
        return $leads;
    }
    
    /**
     * Get lead count
     */
    public static function get_lead_count($form_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        if ($form_id) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE form_id = %d",
                $form_id
            ));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    /**
     * Delete lead
     */
    public static function delete_lead($lead_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_leads';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $lead_id),
            array('%d')
        );
    }
    
    /**
     * Export leads to CSV
     */
    public static function export_to_csv($form_id = null) {
        $leads = $form_id ? self::get_leads_by_form($form_id, 999999) : self::get_all_leads(999999);
        
        if (empty($leads)) {
            return false;
        }
        
        // Create CSV content
        $csv_data = array();
        
        // Headers
        $headers = array('ID', 'Form ID', 'Page URL', 'IP Address', 'User Agent', 'Created At');
        
        // Get all field names from first lead
        $first_lead = $leads[0];
        if (is_array($first_lead->form_data)) {
            foreach ($first_lead->form_data as $key => $value) {
                $headers[] = ucfirst(str_replace('_', ' ', $key));
            }
        }
        
        $csv_data[] = $headers;
        
        // Data rows
        foreach ($leads as $lead) {
            $row = array(
                $lead->id,
                $lead->form_id,
                $lead->page_url,
                $lead->user_ip,
                $lead->user_agent,
                $lead->created_at
            );
            
            if (is_array($lead->form_data)) {
                foreach ($first_lead->form_data as $key => $value) {
                    $row[] = isset($lead->form_data[$key]) ? $lead->form_data[$key] : '';
                }
            }
            
            $csv_data[] = $row;
        }
        
        return $csv_data;
    }
    
    /**
     * Get user IP address
     */
    private static function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
}
