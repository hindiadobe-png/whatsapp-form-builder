<?php
/**
 * Admin functionality
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Admin {
    
    /**
     * Plugin version
     */
    private $version;
    
    /**
     * Initialize the class
     */
    public function __construct($version) {
        $this->version = $version;
    }
    
    /**
     * Add admin menu
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __('WhatsApp Forms', 'whatsapp-form-builder'),
            __('WhatsApp Forms', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-forms',
            array($this, 'display_forms_page'),
            'dashicons-whatsapp',
            30
        );
        
        // Forms submenu
        add_submenu_page(
            'wfbp-forms',
            __('All Forms', 'whatsapp-form-builder'),
            __('All Forms', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-forms',
            array($this, 'display_forms_page')
        );
        
        // Add new form
        add_submenu_page(
            'wfbp-forms',
            __('Add New Form', 'whatsapp-form-builder'),
            __('Add New Form', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-add-form',
            array($this, 'display_add_form_page')
        );
        
        // Leads
        add_submenu_page(
            'wfbp-forms',
            __('Leads', 'whatsapp-form-builder'),
            __('Leads', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-leads',
            array($this, 'display_leads_page')
        );
        
        // Settings
        add_submenu_page(
            'wfbp-forms',
            __('Settings', 'whatsapp-form-builder'),
            __('Settings', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-settings',
            array($this, 'display_settings_page')
        );
        
        // License (Pro)
        add_submenu_page(
            'wfbp-forms',
            __('License', 'whatsapp-form-builder'),
            __('License', 'whatsapp-form-builder'),
            'manage_options',
            'wfbp-license',
            array($this, 'display_license_page')
        );
    }
    
    /**
     * Display forms page
     */
    public function display_forms_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-form-builder'));
        }
        
        $forms_page = new WFBP_Admin_Forms();
        $forms_page->render();
    }
    
    /**
     * Display add/edit form page
     */
    public function display_add_form_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-form-builder'));
        }
        
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        
        $forms_page = new WFBP_Admin_Forms();
        $forms_page->render_form_builder($form_id);
    }
    
    /**
     * Display leads page
     */
    public function display_leads_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-form-builder'));
        }
        
        $leads_page = new WFBP_Admin_Leads();
        $leads_page->render();
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-form-builder'));
        }
        
        $settings_page = new WFBP_Admin_Settings();
        $settings_page->render();
    }
    
    /**
     * Display license page
     */
    public function display_license_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'whatsapp-form-builder'));
        }
        
        include_once WFBP_PLUGIN_DIR . 'admin/views/license.php';
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'wfbp') !== false) {
            wp_enqueue_style(
                'wfbp-admin',
                WFBP_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                $this->version,
                'all'
            );
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'wfbp') !== false) {
            // jQuery UI for drag and drop
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_enqueue_script(
                'wfbp-admin',
                WFBP_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                $this->version,
                true
            );
            
            // Localize script
            wp_localize_script('wfbp-admin', 'wfbp_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wfbp_admin_nonce'),
                'messages' => array(
                    'delete_confirm' => __('Are you sure you want to delete this item?', 'whatsapp-form-builder'),
                    'save_success' => __('Saved successfully!', 'whatsapp-form-builder'),
                    'save_error' => __('An error occurred while saving.', 'whatsapp-form-builder'),
                ),
            ));
        }
    }
    
    /**
     * AJAX: Save form
     */
    public function ajax_save_form() {
        // Check nonce and capabilities
        check_ajax_referer('wfbp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'whatsapp-form-builder')));
        }
        
        // Get form data
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $form_name = isset($_POST['form_name']) ? sanitize_text_field($_POST['form_name']) : '';
        $form_fields = isset($_POST['form_fields']) ? json_decode(stripslashes($_POST['form_fields']), true) : array();
        $form_settings = isset($_POST['form_settings']) ? json_decode(stripslashes($_POST['form_settings']), true) : array();
        
        // Validate
        if (empty($form_name)) {
            wp_send_json_error(array('message' => __('Form name is required.', 'whatsapp-form-builder')));
        }
        
        // Save form
        $saved_id = WFBP_Form::save_form($form_id, $form_name, $form_fields, $form_settings);
        
        if ($saved_id) {
            wp_send_json_success(array(
                'message' => __('Form saved successfully!', 'whatsapp-form-builder'),
                'form_id' => $saved_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save form.', 'whatsapp-form-builder')));
        }
    }
    
    /**
     * AJAX: Delete form
     */
    public function ajax_delete_form() {
        // Check nonce and capabilities
        check_ajax_referer('wfbp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'whatsapp-form-builder')));
        }
        
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        
        if (!$form_id) {
            wp_send_json_error(array('message' => __('Invalid form ID.', 'whatsapp-form-builder')));
        }
        
        $result = WFBP_Form::delete_form($form_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Form deleted successfully!', 'whatsapp-form-builder')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete form.', 'whatsapp-form-builder')));
        }
    }
    
    /**
     * AJAX: Export leads
     */
    public function ajax_export_leads() {
        // Check nonce and capabilities
        check_ajax_referer('wfbp_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'whatsapp-form-builder'));
        }
        
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        
        $csv_data = WFBP_Lead::export_to_csv($form_id);
        
        if (!$csv_data) {
            wp_die(__('No leads found.', 'whatsapp-form-builder'));
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads-' . date('Y-m-d') . '.csv"');
        
        // Output CSV
        $output = fopen('php://output', 'w');
        foreach ($csv_data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        
        exit;
    }
}
