<?php
/**
 * Main plugin class
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main WhatsApp Form Builder Class
 */
final class WhatsApp_Form_Builder {
    
    /**
     * Plugin version
     */
    protected $version = '1.0.0';
    
    /**
     * The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Admin
        require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-install.php';
        require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-ajax.php';
        require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-form-handler.php';
        require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-shortcodes.php';
        require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-license.php';
        
        if (is_admin()) {
            require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-admin.php';
            require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-forms-list.php';
            require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-form-builder.php';
            require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-settings.php';
            require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-leads.php';
        }
        
        // Public
        require_once WAFB_PLUGIN_DIR . 'public/class-wafb-public.php';
    }
    
    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        // Activation and deactivation
        register_activation_hook(WAFB_PLUGIN_BASENAME, array('WAFB_Install', 'activate'));
        register_deactivation_hook(WAFB_PLUGIN_BASENAME, array('WAFB_Install', 'deactivate'));
        
        // Initialize
        add_action('plugins_loaded', array($this, 'init'), 0);
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize classes
        if (is_admin()) {
            new WAFB_Admin();
        }
        
        new WAFB_Public();
        new WAFB_Ajax();
        new WAFB_Shortcodes();
        new WAFB_License();
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('whatsapp-form-builder', false, dirname(WAFB_PLUGIN_BASENAME) . '/languages/');
    }
    
    /**
     * Get the plugin version
     */
    public function version() {
        return $this->version;
    }
}
