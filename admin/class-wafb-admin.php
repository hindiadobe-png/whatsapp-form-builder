<?php
/**
 * Admin functionality
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Admin Class
 */
class WAFB_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_filter('plugin_action_links_' . WAFB_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }
    
    /**
     * Add admin menu
     */
    public function add_menu() {
        add_menu_page(
            __('WhatsApp Forms', 'whatsapp-form-builder'),
            __('WhatsApp Forms', 'whatsapp-form-builder'),
            'manage_options',
            'wafb-forms',
            array($this, 'forms_page'),
            'dashicons-whatsapp',
            30
        );
        
        add_submenu_page(
            'wafb-forms',
            __('All Forms', 'whatsapp-form-builder'),
            __('All Forms', 'whatsapp-form-builder'),
            'manage_options',
            'wafb-forms',
            array($this, 'forms_page')
        );
        
        add_submenu_page(
            'wafb-forms',
            __('Add New', 'whatsapp-form-builder'),
            __('Add New', 'whatsapp-form-builder'),
            'manage_options',
            'wafb-form-builder',
            array($this, 'form_builder_page')
        );
        
        add_submenu_page(
            'wafb-forms',
            __('Leads', 'whatsapp-form-builder'),
            __('Leads', 'whatsapp-form-builder'),
            'manage_options',
            'wafb-leads',
            array($this, 'leads_page')
        );
        
        add_submenu_page(
            'wafb-forms',
            __('Settings', 'whatsapp-form-builder'),
            __('Settings', 'whatsapp-form-builder'),
            'manage_options',
            'wafb-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, 'wafb-') === false) {
            return;
        }
        
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Enqueue CSS
        wp_enqueue_style('wafb-admin', WAFB_PLUGIN_URL . 'assets/css/admin.css', array(), WAFB_VERSION);
        
        // Enqueue JS
        wp_enqueue_script('wafb-admin', WAFB_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), WAFB_VERSION, true);
        
        // Localize script
        wp_localize_script('wafb-admin', 'wafbAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wafb_admin_nonce'),
            'i18n' => array(
                'delete_confirm' => __('Are you sure you want to delete this?', 'whatsapp-form-builder'),
                'required_field' => __('This field is required', 'whatsapp-form-builder'),
                'saving' => __('Saving...', 'whatsapp-form-builder'),
                'saved' => __('Saved!', 'whatsapp-form-builder'),
                'error' => __('Error', 'whatsapp-form-builder'),
            )
        ));
    }
    
    /**
     * Forms list page
     */
    public function forms_page() {
        require_once WAFB_PLUGIN_DIR . 'admin/views/forms-list.php';
    }
    
    /**
     * Form builder page
     */
    public function form_builder_page() {
        require_once WAFB_PLUGIN_DIR . 'admin/views/form-builder.php';
    }
    
    /**
     * Leads page
     */
    public function leads_page() {
        require_once WAFB_PLUGIN_DIR . 'admin/views/leads.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        require_once WAFB_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wafb-settings') . '">' . __('Settings', 'whatsapp-form-builder') . '</a>',
            '<a href="' . admin_url('admin.php?page=wafb-form-builder') . '">' . __('Add New', 'whatsapp-form-builder') . '</a>',
        );
        
        return array_merge($plugin_links, $links);
    }
}
