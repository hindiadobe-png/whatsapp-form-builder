<?php
/**
 * AJAX handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Ajax Class
 */
class WAFB_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Admin AJAX actions
        add_action('wp_ajax_wafb_save_form', array($this, 'save_form'));
        add_action('wp_ajax_wafb_delete_form', array($this, 'delete_form'));
        add_action('wp_ajax_wafb_duplicate_form', array($this, 'duplicate_form'));
        add_action('wp_ajax_wafb_get_form', array($this, 'get_form'));
        add_action('wp_ajax_wafb_export_leads', array($this, 'export_leads'));
        add_action('wp_ajax_wafb_delete_lead', array($this, 'delete_lead'));
        
        // Public AJAX actions
        add_action('wp_ajax_wafb_submit_form', array($this, 'submit_form'));
        add_action('wp_ajax_nopriv_wafb_submit_form', array($this, 'submit_form'));
    }
    
    /**
     * Save form
     */
    public function save_form() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $fields = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '[]';
        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        
        if (empty($name)) {
            wp_send_json_error(array('message' => __('Form name is required', 'whatsapp-form-builder')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        
        $data = array(
            'name' => $name,
            'fields' => $fields,
            'settings' => $settings,
        );
        
        if ($form_id > 0) {
            // Update existing form
            $wpdb->update($table, $data, array('id' => $form_id));
            $message = __('Form updated successfully', 'whatsapp-form-builder');
        } else {
            // Insert new form
            $wpdb->insert($table, $data);
            $form_id = $wpdb->insert_id;
            $message = __('Form created successfully', 'whatsapp-form-builder');
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'form_id' => $form_id
        ));
    }
    
    /**
     * Delete form
     */
    public function delete_form() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        if ($form_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid form ID', 'whatsapp-form-builder')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        $wpdb->delete($table, array('id' => $form_id));
        
        // Delete associated leads
        $leads_table = $wpdb->prefix . 'wafb_leads';
        $wpdb->delete($leads_table, array('form_id' => $form_id));
        
        wp_send_json_success(array('message' => __('Form deleted successfully', 'whatsapp-form-builder')));
    }
    
    /**
     * Duplicate form
     */
    public function duplicate_form() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        if ($form_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid form ID', 'whatsapp-form-builder')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $form_id), ARRAY_A);
        
        if (!$form) {
            wp_send_json_error(array('message' => __('Form not found', 'whatsapp-form-builder')));
        }
        
        unset($form['id']);
        $form['name'] = $form['name'] . ' (Copy)';
        
        $wpdb->insert($table, $form);
        $new_form_id = $wpdb->insert_id;
        
        wp_send_json_success(array(
            'message' => __('Form duplicated successfully', 'whatsapp-form-builder'),
            'form_id' => $new_form_id
        ));
    }
    
    /**
     * Get form
     */
    public function get_form() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        if ($form_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid form ID', 'whatsapp-form-builder')));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $form_id), ARRAY_A);
        
        if (!$form) {
            wp_send_json_error(array('message' => __('Form not found', 'whatsapp-form-builder')));
        }
        
        wp_send_json_success(array('form' => $form));
    }
    
    /**
     * Export leads
     */
    public function export_leads() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        // Check pro license
        if (get_option('wafb_license_status') !== 'active') {
            wp_send_json_error(array('message' => __('This feature requires a Pro license', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'wafb_leads';
        
        $where = '';
        if ($form_id > 0) {
            $where = $wpdb->prepare(" WHERE form_id = %d", $form_id);
        }
        
        $leads = $wpdb->get_results("SELECT * FROM {$leads_table}{$where} ORDER BY created_at DESC");
        
        wp_send_json_success(array('leads' => $leads));
    }
    
    /**
     * Delete lead
     */
    public function delete_lead() {
        check_ajax_referer('wafb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'whatsapp-form-builder')));
        }
        
        $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
        
        if ($lead_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid lead ID', 'whatsapp-form-builder')));
        }
        
        global $wpdb;
        $leads_table = $wpdb->prefix . 'wafb_leads';
        $wpdb->delete($leads_table, array('id' => $lead_id));
        
        wp_send_json_success(array('message' => __('Lead deleted successfully', 'whatsapp-form-builder')));
    }
    
    /**
     * Submit form (handled by form handler)
     */
    public function submit_form() {
        WAFB_Form_Handler::handle_submission();
    }
}
