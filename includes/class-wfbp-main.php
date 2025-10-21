<?php
/**
 * Main plugin class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Main {
    
    /**
     * The loader
     */
    protected $loader;
    
    /**
     * Plugin version
     */
    protected $version;
    
    /**
     * Initialize the class
     */
    public function __construct() {
        $this->version = WFBP_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-i18n.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-form.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-lead.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-whatsapp.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-validator.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-license.php';
        
        // Admin classes
        require_once WFBP_PLUGIN_DIR . 'admin/class-wfbp-admin.php';
        require_once WFBP_PLUGIN_DIR . 'admin/class-wfbp-admin-forms.php';
        require_once WFBP_PLUGIN_DIR . 'admin/class-wfbp-admin-leads.php';
        require_once WFBP_PLUGIN_DIR . 'admin/class-wfbp-admin-settings.php';
        
        // Public classes
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-public.php';
        require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-shortcode.php';
        
        $this->loader = new WFBP_Loader();
    }
    
    /**
     * Set locale for i18n
     */
    private function set_locale() {
        $plugin_i18n = new WFBP_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    /**
     * Register admin hooks
     */
    private function define_admin_hooks() {
        $plugin_admin = new WFBP_Admin($this->get_version());
        
        // Admin menu and pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_wfbp_save_form', $plugin_admin, 'ajax_save_form');
        $this->loader->add_action('wp_ajax_wfbp_delete_form', $plugin_admin, 'ajax_delete_form');
        $this->loader->add_action('wp_ajax_wfbp_export_leads', $plugin_admin, 'ajax_export_leads');
    }
    
    /**
     * Register public hooks
     */
    private function define_public_hooks() {
        $plugin_public = new WFBP_Public($this->get_version());
        
        // Public styles and scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // AJAX handlers for public
        $this->loader->add_action('wp_ajax_wfbp_submit_form', $plugin_public, 'ajax_submit_form');
        $this->loader->add_action('wp_ajax_nopriv_wfbp_submit_form', $plugin_public, 'ajax_submit_form');
        
        // Shortcode
        $plugin_shortcode = new WFBP_Shortcode();
        $this->loader->add_action('init', $plugin_shortcode, 'register_shortcode');
    }
    
    /**
     * Run the loader
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Get version
     */
    public function get_version() {
        return $this->version;
    }
}
